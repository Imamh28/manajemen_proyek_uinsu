<?php
// app/models/TahapanAktifModel.php

class TahapanAktifModel
{
    public function __construct(private PDO $pdo) {}

    public function hasSchedule(string $idProyek): bool
    {
        if ($idProyek === '') return false;
        $st = $this->pdo->prepare("SELECT 1 FROM jadwal_proyeks WHERE proyek_id_proyek = :p LIMIT 1");
        $st->execute([':p' => $idProyek]);
        return (bool)$st->fetchColumn();
    }

    public function hasPendingRequest(string $proyekId): bool
    {
        $st = $this->pdo->prepare("
            SELECT 1
              FROM tahapan_update_requests
             WHERE proyek_id_proyek = :p
               AND status = 'pending'
             LIMIT 1
        ");
        $st->execute([':p' => $proyekId]);
        return (bool)$st->fetchColumn();
    }

    private function nextTahapanTemplate(string $proyekId): ?string
    {
        $all = $this->pdo->query("SELECT id_tahapan FROM daftar_tahapans ORDER BY id_tahapan ASC")
            ->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $st = $this->pdo->prepare("SELECT daftar_tahapans_id_tahapan FROM jadwal_proyeks WHERE proyek_id_proyek = :p");
        $st->execute([':p' => $proyekId]);
        $used = $st->fetchAll(PDO::FETCH_COLUMN) ?: [];

        foreach ($all as $id) {
            if (!in_array($id, $used, true)) return (string)$id;
        }
        return null;
    }

    private function syncCurrentTahapanId(string $idProyek): void
    {
        if ($idProyek === '') return;

        $st0 = $this->pdo->prepare("SELECT 1 FROM jadwal_proyeks WHERE proyek_id_proyek = :p LIMIT 1");
        $st0->execute([':p' => $idProyek]);
        if (!$st0->fetchColumn()) {
            $up0 = $this->pdo->prepare("UPDATE proyek SET current_tahapan_id = NULL WHERE id_proyek = :p");
            $up0->execute([':p' => $idProyek]);
            return;
        }

        // 1) kalau ada tahapan berjalan (mulai ada, selesai kosong) → itu current
        $st1 = $this->pdo->prepare("
            SELECT daftar_tahapans_id_tahapan
              FROM jadwal_proyeks
             WHERE proyek_id_proyek = :p
               AND mulai IS NOT NULL AND mulai <> ''
               AND (selesai IS NULL OR selesai = '')
             ORDER BY daftar_tahapans_id_tahapan ASC, id_jadwal ASC
             LIMIT 1
        ");
        $st1->execute([':p' => $idProyek]);
        $cur = $st1->fetchColumn();
        if ($cur) {
            $up = $this->pdo->prepare("UPDATE proyek SET current_tahapan_id = :t WHERE id_proyek = :p");
            $up->execute([':t' => (string)$cur, ':p' => $idProyek]);
            return;
        }

        // 2) kalau semua jadwal selesai, tapi masih ada tahapan master belum dibuat jadwal → set ke next tahapan master
        $next = $this->nextTahapanTemplate($idProyek);
        if ($next) {
            $up = $this->pdo->prepare("UPDATE proyek SET current_tahapan_id = :t WHERE id_proyek = :p");
            $up->execute([':t' => $next, ':p' => $idProyek]);
            return;
        }

        // 3) semua selesai (master habis & jadwal selesai) → null
        $up3 = $this->pdo->prepare("UPDATE proyek SET current_tahapan_id = NULL WHERE id_proyek = :p");
        $up3->execute([':p' => $idProyek]);
    }

    public function syncProjectStatusFromSchedules(string $idProyek): void
    {
        // Menunggu -> Berjalan jika ada jadwal mulai
        $st1 = $this->pdo->prepare("
            UPDATE proyek p
               SET p.status = 'Berjalan'
             WHERE p.id_proyek = :p
               AND p.status = 'Menunggu'
               AND EXISTS (
                    SELECT 1
                      FROM jadwal_proyeks jp
                     WHERE jp.proyek_id_proyek = p.id_proyek
                       AND jp.mulai IS NOT NULL
               )
        ");
        $st1->execute([':p' => $idProyek]);

        // ✅ Selesai hanya jika:
        // (1) jumlah tahapan master == jumlah tahapan yang pernah dijadwalkan
        // (2) tidak ada jadwal yang belum selesai
        $st2 = $this->pdo->prepare("
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
        $st2->execute([':p' => $idProyek]);

        $this->syncCurrentTahapanId($idProyek);
    }

    // ✅ eligible = tahapan yang sedang berjalan (mulai terisi, selesai kosong)
    private function computeEligibleTahapId(string $idProyek, array $rows): ?string
    {
        if (!$rows) return null;

        // kalau ada request pending -> tidak ada eligible
        if ($this->hasPendingRequest($idProyek)) return null;

        // ambil tahapan pertama yang belum selesai (urut tahapan)
        foreach ($rows as $r) {
            $mulai   = $r['mulai'] ?? null;
            $selesai = $r['selesai'] ?? null;

            if (empty($selesai)) {
                // hanya eligible jika sudah mulai
                if (!empty($mulai)) {
                    return (string)$r['id_tahapan'];
                }
                return null;
            }
        }

        return null;
    }

    public function stepsByProject(string $idProyek): array
    {
        $sql = "SELECT jp.id_jadwal, jp.plan_mulai, jp.mulai, jp.plan_selesai, jp.selesai, jp.durasi,
                       jp.status, jp.proyek_id_proyek, jp.daftar_tahapans_id_tahapan AS id_tahapan,
                       dt.nama_tahapan
                  FROM jadwal_proyeks jp
                  JOIN daftar_tahapans dt ON dt.id_tahapan = jp.daftar_tahapans_id_tahapan
                 WHERE jp.proyek_id_proyek = :p
              ORDER BY dt.id_tahapan ASC, jp.id_jadwal ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':p' => $idProyek]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->syncProjectStatusFromSchedules($idProyek);

        $eligible = $this->computeEligibleTahapId($idProyek, $rows);
        foreach ($rows as &$r) {
            $r['_eligible'] = ($eligible !== null && (string)$r['id_tahapan'] === (string)$eligible);
        }
        unset($r);

        return $rows;
    }

    public function createRequest(string $proyekId, string $newTahap, string $userId, string $note = ''): bool
    {
        if ($this->hasPendingRequest($proyekId)) return false;

        $rows = $this->stepsByProject($proyekId);
        $eligible = $this->computeEligibleTahapId($proyekId, $rows);
        if ($eligible === null || (string)$eligible !== (string)$newTahap) return false;

        $st = $this->pdo->prepare(
            "INSERT INTO tahapan_update_requests
               (proyek_id_proyek, requested_tahapan_id, requested_by, status, request_note, requested_at)
             VALUES (:p, :t, :u, 'pending', :n, NOW())"
        );
        return $st->execute([
            ':p' => $proyekId,
            ':t' => $newTahap,
            ':u' => $userId,
            ':n' => $note
        ]);
    }

    public function pendingForUser(string $userId, ?string $projectId = null): array
    {
        $sql = "SELECT r.*,
                       p.nama_proyek, d.nama_tahapan, k.nama_karyawan AS requested_by_name
                  FROM tahapan_update_requests r
                  JOIN proyek p ON p.id_proyek = r.proyek_id_proyek
                  JOIN daftar_tahapans d ON d.id_tahapan = r.requested_tahapan_id
                  JOIN karyawan k ON k.id_karyawan = r.requested_by
                 WHERE r.requested_by = :u
                   AND r.status = 'pending'"
            . ($projectId ? " AND r.proyek_id_proyek = :pid" : "") . "
              ORDER BY r.requested_at DESC";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':u', $userId);
        if ($projectId) $st->bindValue(':pid', $projectId);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function recentForUser(string $userId, int $limit = 10, ?string $projectId = null): array
    {
        $sql = "SELECT r.*,
                       p.nama_proyek, d.nama_tahapan,
                       req.nama_karyawan AS requested_by_name,
                       rev.nama_karyawan AS reviewed_by_name
                  FROM tahapan_update_requests r
                  JOIN proyek p ON p.id_proyek = r.proyek_id_proyek
                  JOIN daftar_tahapans d ON d.id_tahapan = r.requested_tahapan_id
                  JOIN karyawan req ON req.id_karyawan = r.requested_by
             LEFT JOIN karyawan rev ON rev.id_karyawan = r.reviewed_by
                 WHERE r.requested_by = :u
                   AND r.status <> 'pending'"
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
}
