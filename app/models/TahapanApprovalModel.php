<?php
// app/models/TahapanApprovalModel.php

require_once __DIR__ . '/PenjadwalanModel.php';
require_once __DIR__ . '/ProyekModel.php';

class TahapanApprovalModel
{
    private PDO $pdo;

    private array $lockedProjectStatuses = ['Selesai', 'Dibatalkan'];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function normalizeNote(?string $note): ?string
    {
        if ($note === null) return null;
        $note = trim($note);
        return $note === '' ? null : $note;
    }

    private function isProjectLocked(string $proyekId): bool
    {
        $st = $this->pdo->prepare("SELECT status FROM proyek WHERE id_proyek = :p LIMIT 1");
        $st->execute([':p' => $proyekId]);
        $status = (string)($st->fetchColumn() ?: '');
        return in_array($status, $this->lockedProjectStatuses, true);
    }

    public function pending(): array
    {
        $sql = "SELECT r.*, p.nama_proyek, t.nama_tahapan, k.nama_karyawan AS requested_by_name
                  FROM tahapan_update_requests r
                  JOIN proyek p ON p.id_proyek = r.proyek_id_proyek
                  JOIN daftar_tahapans t ON t.id_tahapan = r.requested_tahapan_id
                  JOIN karyawan k ON k.id_karyawan = r.requested_by
                 WHERE r.status = 'pending'
              ORDER BY r.requested_at ASC";

        $st = $this->pdo->prepare($sql);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function recent(int $limit = 20): array
    {
        $st = $this->pdo->prepare(
            "SELECT r.*, p.nama_proyek, t.nama_tahapan,
                    k.nama_karyawan AS requested_by_name,
                    a.nama_karyawan AS reviewed_by_name
               FROM tahapan_update_requests r
               JOIN proyek p ON p.id_proyek = r.proyek_id_proyek
               JOIN daftar_tahapans t ON t.id_tahapan = r.requested_tahapan_id
               JOIN karyawan k ON k.id_karyawan = r.requested_by
          LEFT JOIN karyawan a ON a.id_karyawan = r.reviewed_by
           ORDER BY r.requested_at DESC
              LIMIT :lim"
        );

        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function approve(int $id, string $approverId, ?string $reviewNote = null): bool
    {
        $reviewNote = $this->normalizeNote($reviewNote);

        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                "SELECT *
                   FROM tahapan_update_requests
                  WHERE id = :id
                    AND status = 'pending'
                  FOR UPDATE"
            );
            $st->execute([':id' => $id]);
            $req = $st->fetch(PDO::FETCH_ASSOC);

            if (!$req) {
                $this->pdo->rollBack();
                return false;
            }

            $pid = (string)($req['proyek_id_proyek'] ?? '');
            $tid = (string)($req['requested_tahapan_id'] ?? '');

            if ($pid === '' || $tid === '') {
                $this->pdo->rollBack();
                return false;
            }

            if ($this->isProjectLocked($pid)) {
                $this->pdo->rollBack();
                return false;
            }

            // pastikan jadwalnya memang ada
            $chk = $this->pdo->prepare(
                "SELECT mulai, selesai
                   FROM jadwal_proyeks
                  WHERE proyek_id_proyek = :p
                    AND daftar_tahapans_id_tahapan = :t
                  LIMIT 1
                  FOR UPDATE"
            );
            $chk->execute([':p' => $pid, ':t' => $tid]);
            $jr = $chk->fetch(PDO::FETCH_ASSOC);

            if (!$jr) {
                $this->pdo->rollBack();
                return false;
            }

            // ✅ jaga urutan: yang boleh di-approve hanya tahapan pertama yang belum selesai
            $curUnfinished = $this->pdo->prepare("
                SELECT daftar_tahapans_id_tahapan
                  FROM jadwal_proyeks
                 WHERE proyek_id_proyek = :p
                   AND (selesai IS NULL OR selesai = '')
                 ORDER BY daftar_tahapans_id_tahapan ASC, id_jadwal ASC
                 LIMIT 1
                 FOR UPDATE
            ");
            $curUnfinished->execute([':p' => $pid]);
            $shouldBe = (string)($curUnfinished->fetchColumn() ?: '');
            if ($shouldBe !== '' && $shouldBe !== $tid) {
                $this->pdo->rollBack();
                return false;
            }

            // jika mulai kosong (kasus data lama), isi mulai dulu agar konsisten
            if (empty($jr['mulai'])) {
                $upStart = $this->pdo->prepare("
                    UPDATE jadwal_proyeks
                       SET mulai = CURDATE()
                     WHERE proyek_id_proyek = :p
                       AND daftar_tahapans_id_tahapan = :t
                ");
                $upStart->execute([':p' => $pid, ':t' => $tid]);
            }

            // ✅ inti: set selesai untuk tahapan ini
            $upDone = $this->pdo->prepare("
                UPDATE jadwal_proyeks
                   SET selesai = CURDATE()
                 WHERE proyek_id_proyek = :p
                   AND daftar_tahapans_id_tahapan = :t
                   AND (selesai IS NULL OR selesai = '')
            ");
            $upDone->execute([':p' => $pid, ':t' => $tid]);

            // update request
            $ap = $this->pdo->prepare(
                "UPDATE tahapan_update_requests
                    SET status      = 'approved',
                        reviewed_by = :u,
                        reviewed_at = NOW(),
                        review_note = :n
                  WHERE id = :id"
            );
            $ap->execute([
                ':u'  => $approverId,
                ':n'  => $reviewNote,
                ':id' => $id
            ]);

            // recalc status jadwal
            (new PenjadwalanModel($this->pdo))->recalcStatusForProject($pid);

            // ✅ FINAL: sync proyek (status + current_tahapan_id)
            (new ProyekModel($this->pdo))->syncStateAfterRelationsChange($pid, $this->lockedProjectStatuses);

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function reject(int $id, string $approverId, ?string $reviewNote): bool
    {
        $reviewNote = $this->normalizeNote($reviewNote);

        $st = $this->pdo->prepare(
            "UPDATE tahapan_update_requests
                SET status      = 'rejected',
                    reviewed_by = :u,
                    reviewed_at = NOW(),
                    review_note = :n
              WHERE id = :id
                AND status = 'pending'"
        );

        return $st->execute([
            ':u'  => $approverId,
            ':n'  => $reviewNote,
            ':id' => $id
        ]);
    }
}
