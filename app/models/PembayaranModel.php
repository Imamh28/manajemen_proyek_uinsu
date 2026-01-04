<?php
// app/models/PembayaranModel.php

class PembayaranModel
{
    public function __construct(private PDO $pdo) {}

    /**
     * Ambil daftar pembayaran.
     * - Jika $picSalesKaryawanId = null => tampilkan semua
     * - Jika ada nilainya => hanya pembayaran dari proyek yang PIC Sales-nya user tsb
     *
     * ✅ status_pembayaran_auto dihitung dari:
     *    SUM(total_pembayaran) per proyek dibanding total_biaya_proyek (bukan tanggal bayar)
     */
    public function all(string $q = '', ?string $picSalesKaryawanId = null): array
    {
        $sql = "
            SELECT
                pb.*,
                pr.nama_proyek,
                CASE
                    WHEN COALESCE(pr.total_biaya_proyek,0) > 0
                         AND COALESCE(pr_sum.paid_total,0) >= COALESCE(pr.total_biaya_proyek,0)
                    THEN 'Lunas'
                    ELSE 'Belum Lunas'
                END AS status_pembayaran_auto
            FROM pembayarans pb
            LEFT JOIN proyek pr ON pb.proyek_id_proyek = pr.id_proyek
            LEFT JOIN (
                SELECT proyek_id_proyek, COALESCE(SUM(total_pembayaran),0) AS paid_total
                FROM pembayarans
                GROUP BY proyek_id_proyek
            ) pr_sum ON pr_sum.proyek_id_proyek = pr.id_proyek
            WHERE 1=1
        ";
        $bind = [];

        if ($picSalesKaryawanId !== null && $picSalesKaryawanId !== '') {
            $sql .= " AND pr.karyawan_id_pic_sales = :ks";
            $bind[':ks'] = $picSalesKaryawanId;
        }

        if ($q !== '') {
            $sql .= " AND (
                        pb.id_pem_bayaran LIKE :q
                        OR pr.nama_proyek LIKE :q
                        OR pb.jenis_pembayaran LIKE :q
                        OR pb.status_pembayaran LIKE :q
                     )";
            $bind[':q'] = "%{$q}%";
        }

        $sql .= " ORDER BY pb.id_pem_bayaran DESC";

        $st = $this->pdo->prepare($sql);
        $st->execute($bind);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(string $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM pembayarans WHERE id_pem_bayaran = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function create(array $d): bool
    {
        $st = $this->pdo->prepare(
            "INSERT INTO pembayarans
             (id_pem_bayaran, jenis_pembayaran, sub_total, pajak_pembayaran, total_pembayaran,
              tanggal_jatuh_tempo, tanggal_bayar, status_pembayaran, bukti_pembayaran, proyek_id_proyek)
             VALUES
             (:id, :jenis, :sub, :pajak, :total, :jt, :tb, :status, :bukti, :prj)"
        );

        return $st->execute([
            ':id'     => $d['id_pem_bayaran'],
            ':jenis'  => $d['jenis_pembayaran'],
            ':sub'    => (float)$d['sub_total'],
            ':pajak'  => (float)$d['pajak_pembayaran'],
            ':total'  => (float)$d['total_pembayaran'],
            ':jt'     => $d['tanggal_jatuh_tempo'],
            ':tb'     => $d['tanggal_bayar'],
            ':status' => $d['status_pembayaran'], // akan disinkronkan lagi via syncStatusByProject
            ':bukti'  => $d['bukti_pembayaran'],
            ':prj'    => $d['proyek_id_proyek'],
        ]);
    }

    public function update(string $id, array $d): bool
    {
        $st = $this->pdo->prepare(
            "UPDATE pembayarans SET
                 jenis_pembayaran = :jenis,
                 sub_total = :sub,
                 pajak_pembayaran = :pajak,
                 total_pembayaran = :total,
                 tanggal_jatuh_tempo = :jt,
                 tanggal_bayar = :tb,
                 status_pembayaran = :status,
                 bukti_pembayaran = :bukti,
                 proyek_id_proyek = :prj
             WHERE id_pem_bayaran = :id"
        );

        return $st->execute([
            ':id'     => $id,
            ':jenis'  => $d['jenis_pembayaran'],
            ':sub'    => (float)$d['sub_total'],
            ':pajak'  => (float)$d['pajak_pembayaran'],
            ':total'  => (float)$d['total_pembayaran'],
            ':jt'     => $d['tanggal_jatuh_tempo'],
            ':tb'     => $d['tanggal_bayar'],
            ':status' => $d['status_pembayaran'], // akan disinkronkan lagi via syncStatusByProject
            ':bukti'  => $d['bukti_pembayaran'],
            ':prj'    => $d['proyek_id_proyek'],
        ]);
    }

    public function delete(string $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM pembayarans WHERE id_pem_bayaran = :id");
        return $st->execute([':id' => $id]);
    }

    public function existsId(string $id): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM pembayarans WHERE id_pem_bayaran = :id LIMIT 1");
        $st->execute([':id' => $id]);
        return (bool)$st->fetchColumn();
    }

    public function existingIds(): array
    {
        return $this->pdo->query("SELECT id_pem_bayaran FROM pembayarans")
            ->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public function projects(?string $picSalesKaryawanId = null): array
    {
        $sql = "SELECT id_proyek, nama_proyek FROM proyek WHERE 1=1";
        $bind = [];

        if ($picSalesKaryawanId !== null && $picSalesKaryawanId !== '') {
            $sql .= " AND karyawan_id_pic_sales = :ks";
            $bind[':ks'] = $picSalesKaryawanId;
        }

        $sql .= " ORDER BY id_proyek DESC";

        $st = $this->pdo->prepare($sql);
        $st->execute($bind);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function projectsWithMeta(?string $picSalesKaryawanId = null): array
    {
        $sql = "SELECT pr.id_proyek,
                       pr.nama_proyek,
                       pr.total_biaya_proyek,
                       COALESCE(SUM(pb.total_pembayaran), 0) AS paid_total
                  FROM proyek pr
             LEFT JOIN pembayarans pb ON pb.proyek_id_proyek = pr.id_proyek
                 WHERE 1=1";
        $bind = [];

        if ($picSalesKaryawanId !== null && $picSalesKaryawanId !== '') {
            $sql .= " AND pr.karyawan_id_pic_sales = :ks";
            $bind[':ks'] = $picSalesKaryawanId;
        }

        $sql .= " GROUP BY pr.id_proyek, pr.nama_proyek, pr.total_biaya_proyek
                  ORDER BY pr.id_proyek DESC";

        $st = $this->pdo->prepare($sql);
        $st->execute($bind);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function exportRows(string $q = '', ?string $picSalesKaryawanId = null): array
    {
        $rows = $this->all($q, $picSalesKaryawanId);

        return array_map(function ($r) {
            return [
                'ID'            => $r['id_pem_bayaran'],
                'Proyek'        => $r['nama_proyek'] ?? '',
                'Jenis'         => $r['jenis_pembayaran'] ?? '',
                'Sub Total'     => $r['sub_total'] ?? '',
                'Pajak'         => $r['pajak_pembayaran'] ?? '',
                'Total'         => $r['total_pembayaran'] ?? '',
                'Jatuh Tempo'   => $r['tanggal_jatuh_tempo'] ?? '',
                'Tanggal Bayar' => $r['tanggal_bayar'] ?? '',
                // ✅ pakai status otomatis, bukan manual
                'Status'        => $r['status_pembayaran_auto'] ?? ($r['status_pembayaran'] ?? ''),
            ];
        }, $rows);
    }

    public function projectTotalBiaya(string $proyekId): int
    {
        $st = $this->pdo->prepare("SELECT total_biaya_proyek FROM proyek WHERE id_proyek = :id LIMIT 1");
        $st->execute([':id' => $proyekId]);
        $v = $st->fetchColumn();
        return (int)($v ?? 0);
    }

    public function sumTotalPembayaranByProyek(string $proyekId, ?string $excludePaymentId = null): int
    {
        $sql = "SELECT COALESCE(SUM(total_pembayaran), 0)
                  FROM pembayarans
                 WHERE proyek_id_proyek = :pid";
        $bind = [':pid' => $proyekId];

        if ($excludePaymentId !== null && $excludePaymentId !== '') {
            $sql .= " AND id_pem_bayaran <> :ex";
            $bind[':ex'] = $excludePaymentId;
        }

        $st = $this->pdo->prepare($sql);
        $st->execute($bind);
        $sum = (float)($st->fetchColumn() ?? 0);

        return (int) round($sum);
    }

    /**
     * ✅ FINAL: Sinkronkan status_pembayaran untuk SEMUA pembayaran dalam 1 proyek.
     * Status proyek lunas ditentukan dari: SUM(total_pembayaran) >= total_biaya_proyek
     * (bukan tanggal bayar, cocok DP/Termin multi pembayaran)
     */
    public function syncStatusByProject(string $proyekId): void
    {
        $proyekId = trim($proyekId);
        if ($proyekId === '') return;

        $total = $this->projectTotalBiaya($proyekId);
        $paid  = $this->sumTotalPembayaranByProyek($proyekId, null);

        $status = ($total > 0 && $paid >= $total) ? 'Lunas' : 'Belum Lunas';

        $st = $this->pdo->prepare("UPDATE pembayarans SET status_pembayaran = :s WHERE proyek_id_proyek = :p");
        $st->execute([':s' => $status, ':p' => $proyekId]);
    }
}
