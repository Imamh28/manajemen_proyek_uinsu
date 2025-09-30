<?php
// app/models/ProyekModel.php
class ProyekModel
{
    public function __construct(private PDO $pdo) {}

    public function all(string $search = ''): array
    {
        $sql = "SELECT p.id_proyek, p.nama_proyek, p.total_biaya_proyek, p.status,
                       k.nama_klien, b.nama_brand,
                       s.nama_karyawan AS nama_sales, t.nama_karyawan AS nama_site
                  FROM proyek p
             LEFT JOIN klien k     ON p.klien_id_klien = k.id_klien
             LEFT JOIN brand b     ON p.brand_id_brand = b.id_brand
             LEFT JOIN karyawan s  ON p.karyawan_id_pic_sales = s.id_karyawan
             LEFT JOIN karyawan t  ON p.karyawan_id_pic_site  = t.id_karyawan
                 WHERE 1=1";
        $bind = [];
        if ($search !== '') {
            $sql .= " AND (p.id_proyek LIKE :q
                        OR p.nama_proyek LIKE :q
                        OR k.nama_klien LIKE :q
                        OR b.nama_brand LIKE :q
                        OR s.nama_karyawan LIKE :q)";
            $bind[':q'] = "%{$search}%";
        }
        $sql .= " ORDER BY p.id_proyek DESC";
        $st = $this->pdo->prepare($sql);
        $st->execute($bind);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(string $id): array|null
    {
        $st = $this->pdo->prepare(
            "SELECT * FROM proyek WHERE id_proyek = :id"
        );
        $st->execute([':id' => $id]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // dropdown sources
    public function clients(): array
    {
        return $this->pdo->query("SELECT id_klien, nama_klien FROM klien ORDER BY nama_klien")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function brands(): array
    {
        return $this->pdo->query("SELECT id_brand, nama_brand FROM brand ORDER BY nama_brand")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function employees(): array
    {
        return $this->pdo->query("SELECT id_karyawan, nama_karyawan FROM karyawan ORDER BY nama_karyawan")->fetchAll(PDO::FETCH_ASSOC);
    }

    // uniqueness helpers
    public function existsId(string $id): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM proyek WHERE id_proyek = :id");
        $st->execute([':id' => $id]);
        return (bool)$st->fetchColumn();
    }
    public function existsName(string $name, ?string $exceptId = null): bool
    {
        if ($exceptId) {
            $st = $this->pdo->prepare("SELECT 1 FROM proyek WHERE LOWER(nama_proyek)=LOWER(:n) AND id_proyek<>:id");
            $st->execute([':n' => $name, ':id' => $exceptId]);
        } else {
            $st = $this->pdo->prepare("SELECT 1 FROM proyek WHERE LOWER(nama_proyek)=LOWER(:n)");
            $st->execute([':n' => $name]);
        }
        return (bool)$st->fetchColumn();
    }

    public function create(array $d): bool
    {
        $st = $this->pdo->prepare(
            "INSERT INTO proyek
         (id_proyek, nama_proyek, deskripsi, total_biaya_proyek, alamat,
          tanggal_mulai, tanggal_selesai, status,
          brand_id_brand, karyawan_id_pic_sales, karyawan_id_pic_site, klien_id_klien,
          quotation, gambar_kerja)
         VALUES
         (:id, :nama, :desk, :biaya, :alamat,
          :tgl_mulai, :tgl_selesai, :status,
          :brand, :sales, :site, :klien,
          :quo, :img)"
        );
        return $st->execute([
            ':id'         => $d['id_proyek'],
            ':nama'       => $d['nama_proyek'],
            ':desk'       => $d['deskripsi'] ?? '',
            ':biaya'      => $d['total_biaya_proyek'],
            ':alamat'     => $d['alamat'] ?? '',
            ':tgl_mulai'  => $d['tanggal_mulai'] ?? '',
            ':tgl_selesai' => $d['tanggal_selesai'] ?? '',
            ':status'     => $d['status'],
            ':brand'      => $d['brand_id_brand'],
            ':sales'      => $d['karyawan_id_pic_sales'],
            ':site'       => $d['karyawan_id_pic_site'],
            ':klien'      => $d['klien_id_klien'],
            ':quo'        => $d['quotation'] ?? '',
            ':img'        => $d['gambar_kerja'] ?? '',
        ]);
    }

    public function update(string $id, array $d): bool
    {
        $st = $this->pdo->prepare(
            "UPDATE proyek SET
            nama_proyek = :nama,
            deskripsi   = :desk,
            total_biaya_proyek = :biaya,
            alamat      = :alamat,
            tanggal_mulai   = :tgl_mulai,
            tanggal_selesai = :tgl_selesai,
            status      = :status,
            brand_id_brand = :brand,
            karyawan_id_pic_sales = :sales,
            karyawan_id_pic_site  = :site,
            klien_id_klien = :klien,
            gambar_kerja   = :img
         WHERE id_proyek = :id"
        );
        return $st->execute([
            ':id'         => $id,
            ':nama'       => $d['nama_proyek'],
            ':desk'       => $d['deskripsi'] ?? '',
            ':biaya'      => $d['total_biaya_proyek'],
            ':alamat'     => $d['alamat'] ?? '',
            ':tgl_mulai'  => $d['tanggal_mulai'] ?? '',
            ':tgl_selesai' => $d['tanggal_selesai'] ?? '',
            ':status'     => $d['status'],
            ':brand'      => $d['brand_id_brand'],
            ':sales'      => $d['karyawan_id_pic_sales'],
            ':site'       => $d['karyawan_id_pic_site'],
            ':klien'      => $d['klien_id_klien'],
            ':img'        => $d['gambar_kerja'] ?? '',
        ]);
    }

    public function canDelete(string $id): bool
    {
        // Ada FK dari pembayarans & jadwal_proyeks → jangan hapus kalau masih dipakai
        $q1 = $this->pdo->prepare("SELECT COUNT(*) FROM pembayarans WHERE proyek_id_proyek = :id");
        $q2 = $this->pdo->prepare("SELECT COUNT(*) FROM jadwal_proyeks WHERE proyek_id_proyek = :id");
        $q1->execute([':id' => $id]);
        $q2->execute([':id' => $id]);
        return ($q1->fetchColumn() == 0 && $q2->fetchColumn() == 0);
    }

    public function delete(string $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM proyek WHERE id_proyek = :id");
        return $st->execute([':id' => $id]);
    }

    // for export
    public function exportRows(string $search = ''): array
    {
        return $this->all($search);
    }

    // for live validation arrays
    public function existingIds(): array
    {
        return $this->pdo->query("SELECT id_proyek FROM proyek")->fetchAll(PDO::FETCH_COLUMN);
    }
    public function existingNames(): array
    {
        return $this->pdo->query("SELECT nama_proyek FROM proyek")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function generateQuotationCode(?int $year = null): string
    {
        $y = $year ?: (int)date('Y');
        $like = "QUO/{$y}/%";
        $st = $this->pdo->prepare("SELECT quotation FROM proyek WHERE quotation LIKE :p");
        $st->execute([':p' => $like]);
        $rows = $st->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $max = 0;
        foreach ($rows as $q) {
            // format QUO/YYYY/NNN
            $parts = explode('/', $q);
            $seq   = (int)($parts[2] ?? 0);
            if ($seq > $max) $max = $seq;
        }
        $next = str_pad((string)($max + 1), 3, '0', STR_PAD_LEFT);
        return "QUO/{$y}/{$next}";
    }
}
