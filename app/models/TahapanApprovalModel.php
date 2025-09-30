<?php
// app/models/TahapanApprovalModel.php
require_once __DIR__ . '/PenjadwalanModel.php';

class TahapanApprovalModel
{
    /** @var PDO */
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        // pastikan tidak null
        $this->pdo = $pdo;
    }

    /** Daftar request pending (tampilkan catatan pengusul = request_note) */
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

    /** Riwayat terakhir (tampilkan request_note & review_note) */
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

    /** Approve + simpan review_note */
    public function approve(int $id, string $approverId, ?string $reviewNote = null): bool
    {
        $this->pdo->beginTransaction();
        try {
            // kunci baris pending
            $st = $this->pdo->prepare("SELECT * FROM tahapan_update_requests WHERE id=:id AND status='pending' FOR UPDATE");
            $st->execute([':id' => $id]);
            $req = $st->fetch(PDO::FETCH_ASSOC);
            if (!$req) {
                $this->pdo->rollBack();
                return false;
            }

            $pid = $req['proyek_id_proyek'];
            $tid = $req['requested_tahapan_id'];

            // tutup tahapan berjalan selain target
            $close = $this->pdo->prepare(
                "UPDATE jadwal_proyeks
                    SET selesai = CURDATE()
                  WHERE proyek_id_proyek = :p
                    AND mulai IS NOT NULL
                    AND selesai IS NULL
                    AND daftar_tahapans_id_tahapan <> :t"
            );
            $close->execute([':p' => $pid, ':t' => $tid]);

            // mulai tahapan target (kalau belum ada mulai)
            $start = $this->pdo->prepare(
                "UPDATE jadwal_proyeks
                    SET mulai = COALESCE(mulai, CURDATE())
                  WHERE proyek_id_proyek = :p AND daftar_tahapans_id_tahapan = :t"
            );
            $start->execute([':p' => $pid, ':t' => $tid]);

            // kalau target adalah tahapan terakhir di proyek → selesai=CURDATE()
            $mx = $this->pdo->prepare("SELECT MAX(daftar_tahapans_id_tahapan) FROM jadwal_proyeks WHERE proyek_id_proyek = :p");
            $mx->execute([':p' => $pid]);
            $lastId = (string)$mx->fetchColumn();
            if ($lastId && $lastId === $tid) {
                $end = $this->pdo->prepare(
                    "UPDATE jadwal_proyeks
                        SET selesai = CURDATE()
                      WHERE proyek_id_proyek = :p AND daftar_tahapans_id_tahapan = :t"
                );
                $end->execute([':p' => $pid, ':t' => $tid]);
            }

            // tandai approved + set review_note
            $ap = $this->pdo->prepare(
                "UPDATE tahapan_update_requests
                    SET status='approved',
                        reviewed_by=:u,
                        reviewed_at=NOW(),
                        review_note=:n
                  WHERE id=:id"
            );
            $ap->execute([':u' => $approverId, ':n' => $reviewNote, ':id' => $id]);

            // hitung ulang status jadwal
            (new PenjadwalanModel($this->pdo))->recalcStatusForProject($pid);

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /** Reject + simpan review_note */
    public function reject(int $id, string $approverId, ?string $reviewNote): bool
    {
        $st = $this->pdo->prepare(
            "UPDATE tahapan_update_requests
                SET status='rejected',
                    reviewed_by=:u,
                    reviewed_at=NOW(),
                    review_note=:n
              WHERE id=:id AND status='pending'"
        );
        return $st->execute([':u' => $approverId, ':n' => $reviewNote, ':id' => $id]);
    }
}
