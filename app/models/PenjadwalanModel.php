<?php
// app/models/PenjadwalanModel.php
class PenjadwalanModel
{
    public function __construct(private PDO $pdo) {}

    private function isValidDateYmd(string $d): bool
    {
        $d = trim($d);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) return false;
        [$y, $m, $day] = array_map('intval', explode('-', $d));
        return checkdate($m, $day, $y);
    }

    public function diffDaysInclusive(string $start, string $end): int
    {
        if (!$this->isValidDateYmd($start) || !$this->isValidDateYmd($end)) return 0;
        $a = strtotime($start);
        $b = strtotime($end);
        if ($a === false || $b === false || $b < $a) return 0;
        return (int)floor(($b - $a) / 86400) + 1;
    }

    // ====== Proyek & dropdowns ======
    public function projects(): array
    {
        $sql = "SELECT id_proyek, nama_proyek FROM proyek ORDER BY id_proyek DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function projectDetail(string $id): ?array
    {
        $st = $this->pdo->prepare(
            "SELECT p.*, k.nama_klien, b.nama_brand,
                    s.nama_karyawan AS nama_sales, t.nama_karyawan AS nama_site
               FROM proyek p
          LEFT JOIN klien k    ON k.id_klien = p.klien_id_klien
          LEFT JOIN brand b    ON b.id_brand = p.brand_id_brand
          LEFT JOIN karyawan s ON s.id_karyawan = p.karyawan_id_pic_sales
          LEFT JOIN karyawan t ON t.id_karyawan = p.karyawan_id_pic_site
              WHERE p.id_proyek = :id"
        );
        $st->execute([':id' => $id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function paymentsByProject(string $id): array
    {
        $st = $this->pdo->prepare(
            "SELECT * FROM pembayarans WHERE proyek_id_proyek = :id ORDER BY id_pem_bayaran ASC"
        );
        $st->execute([':id' => $id]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function all(string $proyekId): array
    {
        $st = $this->pdo->prepare(
            "SELECT j.*, t.nama_tahapan
               FROM jadwal_proyeks j
          LEFT JOIN daftar_tahapans t ON t.id_tahapan = j.daftar_tahapans_id_tahapan
              WHERE j.proyek_id_proyek = :p
           ORDER BY j.plan_mulai ASC, j.id_jadwal ASC"
        );
        $st->execute([':p' => $proyekId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(string $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM jadwal_proyeks WHERE id_jadwal = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function existsId(string $id): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM jadwal_proyeks WHERE id_jadwal = :id");
        $st->execute([':id' => $id]);
        return (bool)$st->fetchColumn();
    }

    public function existingIdsByProject(string $proyekId): array
    {
        $st = $this->pdo->prepare("SELECT id_jadwal FROM jadwal_proyeks WHERE proyek_id_proyek = :p");
        $st->execute([':p' => $proyekId]);
        return $st->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    /** Tentukan tahapan berikutnya yang belum dipakai pada proyek (TH01..TH06). */
    public function nextTahapanId(string $proyekId): ?string
    {
        // ambil semua tahapan (urut id TH01..TH06)
        $all = $this->pdo->query("SELECT id_tahapan FROM daftar_tahapans ORDER BY id_tahapan ASC")
            ->fetchAll(PDO::FETCH_COLUMN) ?: [];

        // yang sudah ada di proyek
        $st = $this->pdo->prepare("SELECT daftar_tahapans_id_tahapan FROM jadwal_proyeks WHERE proyek_id_proyek = :p");
        $st->execute([':p' => $proyekId]);
        $used = $st->fetchAll(PDO::FETCH_COLUMN) ?: [];

        foreach ($all as $id) {
            if (!in_array($id, $used, true)) return $id;
        }
        return null;
    }

    public function create(array $d): bool
    {
        $sql = "INSERT INTO jadwal_proyeks
                (id_jadwal, plan_mulai, plan_selesai, durasi, status, mulai, selesai, proyek_id_proyek, daftar_tahapans_id_tahapan)
                VALUES (:id, :p_mulai, :p_selesai, :durasi, :status, NULL, NULL, :proyek, :tahapan)";
        $st  = $this->pdo->prepare($sql);
        return $st->execute([
            ':id'        => $d['id_jadwal'],
            ':p_mulai'   => $d['plan_mulai'],
            ':p_selesai' => $d['plan_selesai'],
            ':durasi'    => $d['durasi'],
            ':status'    => 'Belum Mulai',
            ':proyek'    => $d['proyek_id_proyek'],
            ':tahapan'   => $d['daftar_tahapans_id_tahapan'],
        ]);
    }

    /** PM hanya boleh update planning & durasi; tidak boleh mengubah tahapan. */
    public function update(string $id, array $d): bool
    {
        $sql = "UPDATE jadwal_proyeks
                   SET plan_mulai = :p_mulai,
                       plan_selesai = :p_selesai,
                       durasi = :durasi
                 WHERE id_jadwal = :id";
        $st  = $this->pdo->prepare($sql);
        return $st->execute([
            ':id'        => $id,
            ':p_mulai'   => $d['plan_mulai'],
            ':p_selesai' => $d['plan_selesai'],
            ':durasi'    => $d['durasi'],
        ]);
    }

    public function delete(string $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM jadwal_proyeks WHERE id_jadwal = :id");
        return $st->execute([':id' => $id]);
    }

    /** Hitung ulang status semua baris jadwal dalam 1 proyek (aturan yang kamu tentukan). */
    public function recalcStatusForProject(string $proyekId): bool
    {
        $sql = "
        UPDATE jadwal_proyeks j
           SET status = CASE
               WHEN j.mulai IS NULL AND CURDATE() < j.plan_mulai THEN 'Belum Mulai'
               WHEN j.mulai IS NULL AND CURDATE() > j.plan_mulai THEN 'Terlambat'
               WHEN j.selesai IS NOT NULL AND j.selesai < j.plan_selesai THEN 'Lebih Cepat'
               WHEN j.selesai IS NOT NULL AND j.selesai > j.plan_selesai THEN 'Terlambat'
               ELSE 'Sesuai Jadwal'
           END
         WHERE j.proyek_id_proyek = :p";
        $st = $this->pdo->prepare($sql);
        return $st->execute([':p' => $proyekId]);
    }
}
