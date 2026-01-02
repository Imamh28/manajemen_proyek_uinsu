<?php
// app/models/ProgresProyekModel.php

declare(strict_types=1);

class ProgresProyekModel
{
    public function __construct(private PDO $pdo) {}

    /**
     * Ambil daftar proyek (ringkas) + relasi klien/sales/site.
     * Sertakan tanggal mulai/selesai & total biaya untuk ringkasan.
     * NOTE: Brand sudah dihapus dari sistem, jadi tidak ada join brand lagi.
     */
    public function all(string $q = ''): array
    {
        $sql = "SELECT
                    p.id_proyek,
                    p.nama_proyek,
                    p.deskripsi,
                    p.total_biaya_proyek,
                    p.status,
                    p.tanggal_mulai,
                    p.tanggal_selesai,
                    k.nama_klien,
                    s.nama_karyawan AS nama_sales,
                    t.nama_karyawan AS nama_site
                FROM proyek p
                LEFT JOIN klien k    ON k.id_klien = p.klien_id_klien
                LEFT JOIN karyawan s ON s.id_karyawan = p.karyawan_id_pic_sales
                LEFT JOIN karyawan t ON t.id_karyawan = p.karyawan_id_pic_site
                WHERE 1=1";
        $bind = [];

        if ($q !== '') {
            $sql .= " AND (
                        p.id_proyek LIKE :q
                        OR p.nama_proyek LIKE :q
                        OR k.nama_klien LIKE :q
                        OR s.nama_karyawan LIKE :q
                        OR t.nama_karyawan LIKE :q
                      )";
            $bind[':q'] = "%{$q}%";
        }

        $sql .= " ORDER BY p.id_proyek DESC";

        $st = $this->pdo->prepare($sql);
        $st->execute($bind);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Ambil tahapan per proyek (sekali query)
        if (!$rows) return [];

        $ids = array_values(array_map(fn($r) => (string)$r['id_proyek'], $rows));
        if (!$ids) return $rows;

        $in = implode(',', array_fill(0, count($ids), '?'));

        $sqlT = "SELECT
                    jp.proyek_id_proyek,
                    dt.nama_tahapan,
                    jp.plan_mulai,
                    jp.plan_selesai,
                    jp.mulai,
                    jp.selesai,
                    jp.status
                 FROM jadwal_proyeks jp
                 JOIN daftar_tahapans dt
                   ON dt.id_tahapan = jp.daftar_tahapans_id_tahapan
                 WHERE jp.proyek_id_proyek IN ($in)
                 ORDER BY jp.plan_mulai ASC";

        $stT = $this->pdo->prepare($sqlT);
        $stT->execute($ids);
        $allTahap = $stT->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Grouping tahapan by proyek
        $map = [];
        foreach ($allTahap as $t) {
            $pid = (string)($t['proyek_id_proyek'] ?? '');
            if ($pid === '') continue;
            $map[$pid][] = $t;
        }

        // sisipkan ke rows
        foreach ($rows as &$r) {
            $pid = (string)($r['id_proyek'] ?? '');
            $r['tahapan'] = $map[$pid] ?? [];
        }
        unset($r);

        return $rows;
    }
}
