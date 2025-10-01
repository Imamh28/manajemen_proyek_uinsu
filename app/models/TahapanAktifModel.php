<?php
// app/models/TahapanAktifModel.php
class TahapanAktifModel
{
    private PDO $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Pilihan proyek */
    public function projectOptions(): array
    {
        $sql = "SELECT id_proyek, nama_proyek FROM proyek ORDER BY id_proyek ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Daftar jadwal (tahapan) per proyek (urut TH01..THxx) + flag _eligible */
    public function stepsByProject(string $idProyek): array
    {
        $sql = "SELECT jp.id_jadwal, jp.plan_mulai, jp.mulai, jp.plan_selesai, jp.selesai, jp.durasi,
                       jp.status, jp.proyek_id_proyek, jp.daftar_tahapans_id_tahapan AS id_tahapan,
                       dt.nama_tahapan
                  FROM jadwal_proyeks jp
                  JOIN daftar_tahapans dt ON dt.id_tahapan = jp.daftar_tahapans_id_tahapan
                 WHERE jp.proyek_id_proyek = :p
              ORDER BY dt.id_tahapan ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':p' => $idProyek]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // hanya tahap pertama yang belum mulai yang eligible diajukan
        $eligible = null;
        foreach ($rows as $r) {
            if (empty($r['mulai'])) {
                $eligible = $r['id_tahapan'];
                break;
            }
        }
        foreach ($rows as &$r) $r['_eligible'] = ($eligible !== null && $r['id_tahapan'] === $eligible);
        unset($r);
        return $rows;
    }

    /** Buat pengajuan (catatan pengusul ke request_note) */
    public function createRequest(string $proyekId, string $newTahap, string $userId, string $note = ''): bool
    {
        $st = $this->pdo->prepare(
            "INSERT INTO tahapan_update_requests
               (proyek_id_proyek, requested_tahapan_id, requested_by, status, request_note, requested_at)
             VALUES (:p, :t, :u, 'pending', :n, NOW())"
        );
        return $st->execute([':p' => $proyekId, ':t' => $newTahap, ':u' => $userId, ':n' => $note]);
    }

    /** Pending milik user (opsional filter proyek) */
    public function pendingForUser(string $userId, ?string $projectId = null): array
    {
        $sql = "SELECT r.*, r.request_note,
                       p.nama_proyek, d.nama_tahapan, k.nama_karyawan AS requested_by_name
                  FROM tahapan_update_requests r
                  JOIN proyek p ON p.id_proyek = r.proyek_id_proyek
                  JOIN daftar_tahapans d ON d.id_tahapan = r.requested_tahapan_id
                  JOIN karyawan k ON k.id_karyawan = r.requested_by
                 WHERE r.requested_by = :u AND r.status='pending'"
            . ($projectId ? " AND r.proyek_id_proyek = :pid" : "") . "
              ORDER BY r.requested_at DESC";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':u', $userId);
        if ($projectId) $st->bindValue(':pid', $projectId);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Riwayat milik user (opsional filter proyek) */
    public function recentForUser(string $userId, int $limit = 10, ?string $projectId = null): array
    {
        $sql = "SELECT r.*, p.nama_proyek, d.nama_tahapan,
                       req.nama_karyawan AS requested_by_name,
                       rev.nama_karyawan AS reviewed_by_name,
                       COALESCE(r.review_note, r.note) AS review_note,
                       COALESCE(r.request_note, r.note) AS request_note
                  FROM tahapan_update_requests r
                  JOIN proyek p ON p.id_proyek = r.proyek_id_proyek
                  JOIN daftar_tahapans d ON d.id_tahapan = r.requested_tahapan_id
                  JOIN karyawan req ON req.id_karyawan = r.requested_by
             LEFT JOIN karyawan rev ON rev.id_karyawan = r.reviewed_by
                 WHERE r.requested_by = :u AND r.status <> 'pending'"
            . ($projectId ? " AND r.proyek_id_proyek = :pid" : "") . "
              ORDER BY r.requested_at DESC
                 LIMIT :lim";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':u', $userId);
        if ($projectId) $st->bindValue(':pid', $projectId);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Ambil proyek terakhir yang diajukan tahapan oleh user (apa pun statusnya). */
    public function lastRequestedProjectForUser(string $userId): ?string
    {
        $st = $this->pdo->prepare(
            "SELECT proyek_id_proyek
               FROM tahapan_update_requests
              WHERE requested_by = :u
           ORDER BY requested_at DESC
              LIMIT 1"
        );
        $st->execute([':u' => $userId]);
        $pid = $st->fetchColumn();
        return $pid ? (string)$pid : null;
    }
}
