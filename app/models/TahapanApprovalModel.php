<?php
// app/models/TahapanApprovalModel.php

require_once __DIR__ . '/PenjadwalanModel.php';

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

    private function ensureProjectStarted(string $proyekId): void
    {
        $st = $this->pdo->prepare(
            "UPDATE proyek
                SET status = 'Berjalan'
              WHERE id_proyek = :p
                AND status = 'Menunggu'"
        );
        $st->execute([':p' => $proyekId]);
    }

    // ✅ Selesai hanya jika semua tahapan master dijadwalkan & selesai
    private function ensureProjectCompletedIfAllDone(string $proyekId): void
    {
        $st = $this->pdo->prepare("
            UPDATE proyek p
               SET p.status = 'Selesai'
             WHERE p.id_proyek = :p
               AND p.status <> 'Selesai'
               AND (SELECT COUNT(*) FROM daftar_tahapans) = (
                    SELECT COUNT(DISTINCT jp.daftar_tahapans_id_tahapan)
                      FROM jadwal_proyeks jp
                     WHERE jp.proyek_id_proyek = p.id_proyek
               )
               AND EXISTS (
                    SELECT 1 FROM jadwal_proyeks jp
                     WHERE jp.proyek_id_proyek = p.id_proyek
               )
               AND NOT EXISTS (
                    SELECT 1
                      FROM jadwal_proyeks jp
                     WHERE jp.proyek_id_proyek = p.id_proyek
                       AND (jp.selesai IS NULL OR jp.selesai = '')
               )
        ");
        $st->execute([':p' => $proyekId]);
    }

    private function setCurrentTahapan(string $proyekId, ?string $tahapanId): void
    {
        $st = $this->pdo->prepare("UPDATE proyek SET current_tahapan_id = :t WHERE id_proyek = :p");
        $st->execute([':t' => $tahapanId, ':p' => $proyekId]);
    }

    private function computeCurrentTahapanAfterApproval(string $proyekId): ?string
    {
        // 1) kalau masih ada jadwal belum selesai dan sudah mulai → itu current
        $st = $this->pdo->prepare("
            SELECT daftar_tahapans_id_tahapan
              FROM jadwal_proyeks
             WHERE proyek_id_proyek = :p
               AND mulai IS NOT NULL AND mulai <> ''
               AND (selesai IS NULL OR selesai = '')
             ORDER BY daftar_tahapans_id_tahapan ASC, id_jadwal ASC
             LIMIT 1
        ");
        $st->execute([':p' => $proyekId]);
        $cur = $st->fetchColumn();
        if ($cur) return (string)$cur;

        // 2) kalau tidak ada yang berjalan, set ke next tahapan master (unscheduled) bila ada
        $pm = new PenjadwalanModel($this->pdo);
        $next = $pm->nextTahapanId($proyekId);
        if ($next) return $next;

        return null;
    }

    public function pending(): array
    {
        $sql = "SELECT r.*, p.nama_proyek, t.nama_tahapan, k.nama_karyawan AS requested_by_name,
                       COALESCE(r.request_note, r.note) AS request_note
                  FROM tahapan_update_requests r
                  JOIN proyek p ON p.id_proyek = r.proyek_id_proyek
                  JOIN daftar_tahapans t ON t.id_tahapan = r.requested_tahapan_id
                  JOIN karyawan k ON k.id_karyawan = r.requested_by
                 WHERE r.status = 'pending'
              ORDER BY r.requested_at ASC";
        $st = $this->pdo->query($sql);
        return $st ? ($st->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
    }

    public function recent(int $limit = 20): array
    {
        $st = $this->pdo->prepare(
            "SELECT r.*, p.nama_proyek, t.nama_tahapan,
                    k.nama_karyawan AS requested_by_name,
                    a.nama_karyawan AS reviewed_by_name,
                    COALESCE(r.request_note, r.note) AS request_note,
                    r.review_note
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
                // ada tahapan sebelumnya yang belum selesai
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

            $this->ensureProjectStarted($pid);
            $this->ensureProjectCompletedIfAllDone($pid);

            // current tahapan setelah approval
            $newCur = $this->computeCurrentTahapanAfterApproval($pid);
            $this->setCurrentTahapan($pid, $newCur);

            // jika proyek sudah selesai → current tahapan = null
            $stClr = $this->pdo->prepare("UPDATE proyek SET current_tahapan_id = NULL WHERE id_proyek = :p AND status = 'Selesai'");
            $stClr->execute([':p' => $pid]);

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
