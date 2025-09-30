<?php
// app/models/KlienModel.php
class KlienModel
{
    public function __construct(private PDO $pdo) {}

    /** list + search (nama/email/telp/id) */
    public function all(string $q = ''): array
    {
        if ($q !== '') {
            $sql = "SELECT * FROM klien
                    WHERE id_klien LIKE :q
                       OR nama_klien LIKE :q
                       OR email_klien LIKE :q
                       OR no_telepon_klien LIKE :q
                    ORDER BY id_klien ASC";
            $st = $this->pdo->prepare($sql);
            $st->execute([':q' => "%{$q}%"]);
        } else {
            $st = $this->pdo->query("SELECT * FROM klien ORDER BY id_klien ASC");
        }
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(string $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM klien WHERE id_klien = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function create(array $d): bool
    {
        $sql = "INSERT INTO klien (id_klien, nama_klien, no_telepon_klien, email_klien, alamat_klien)
                VALUES (:id, :nama, :telp, :email, :alamat)";
        $st = $this->pdo->prepare($sql);
        return $st->execute([
            ':id'     => $d['id_klien'],
            ':nama'   => $d['nama_klien'],
            ':telp'   => $d['no_telepon_klien'],
            ':email'  => $d['email_klien'],
            ':alamat' => $d['alamat_klien'],
        ]);
    }

    public function update(string $id, array $d): bool
    {
        $sql = "UPDATE klien
                   SET nama_klien = :nama,
                       no_telepon_klien = :telp,
                       email_klien = :email,
                       alamat_klien = :alamat
                 WHERE id_klien = :id";
        $st = $this->pdo->prepare($sql);
        return $st->execute([
            ':id'     => $id,
            ':nama'   => $d['nama_klien'],
            ':telp'   => $d['no_telepon_klien'],
            ':email'  => $d['email_klien'],
            ':alamat' => $d['alamat_klien'],
        ]);
    }

    public function delete(string $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM klien WHERE id_klien = :id");
        return $st->execute([':id' => $id]);
    }

    /** referensial: masih dipakai proyek? */
    public function canDelete(string $id): bool
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM proyek WHERE klien_id_klien = :id");
        $st->execute([':id' => $id]);
        return ((int)$st->fetchColumn()) === 0;
    }

    // ==== helpers unik ====
    public function existsId(string $id): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM klien WHERE id_klien = :id LIMIT 1");
        $st->execute([':id' => $id]);
        return (bool)$st->fetchColumn();
    }
    public function existsEmail(string $email): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM klien WHERE email_klien = :e LIMIT 1");
        $st->execute([':e' => $email]);
        return (bool)$st->fetchColumn();
    }
    public function existsNama(string $nama): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM klien WHERE nama_klien = :n LIMIT 1");
        $st->execute([':n' => $nama]);
        return (bool)$st->fetchColumn();
    }
    public function existsPhone(string $phone): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM klien WHERE no_telepon_klien = :p LIMIT 1");
        $st->execute([':p' => $phone]);
        return (bool)$st->fetchColumn();
    }
    public function existsEmailExcept(string $email, string $id): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM klien WHERE email_klien = :e AND id_klien <> :id LIMIT 1");
        $st->execute([':e' => $email, ':id' => $id]);
        return (bool)$st->fetchColumn();
    }
    public function existsNamaExcept(string $nama, string $id): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM klien WHERE nama_klien = :n AND id_klien <> :id LIMIT 1");
        $st->execute([':n' => $nama, ':id' => $id]);
        return (bool)$st->fetchColumn();
    }
    public function existsPhoneExcept(string $phone, string $id): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM klien WHERE no_telepon_klien = :p AND id_klien <> :id LIMIT 1");
        $st->execute([':p' => $phone, ':id' => $id]);
        return (bool)$st->fetchColumn();
    }

    // ==== untuk live-validate (client-side) ====
    public function existingIds(): array
    {
        return $this->pdo->query("SELECT id_klien FROM klien")->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
    public function existingEmails(): array
    {
        return $this->pdo->query("SELECT email_klien FROM klien WHERE email_klien IS NOT NULL AND email_klien<>''")
            ->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
    public function existingNames(): array
    {
        return $this->pdo->query("SELECT nama_klien FROM klien")->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
    public function existingPhones(): array
    {
        return $this->pdo->query("SELECT no_telepon_klien FROM klien WHERE no_telepon_klien IS NOT NULL AND no_telepon_klien<>''")->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    /** rows untuk export */
    public function exportRows(string $q = ''): array
    {
        $rows = $this->all($q);
        // rapihkan kolom urutan
        return array_map(fn($r) => [
            'ID'      => $r['id_klien'],
            'Nama'    => $r['nama_klien'],
            'Telepon' => $r['no_telepon_klien'],
            'Email'   => $r['email_klien'],
            'Alamat'  => $r['alamat_klien'],
        ], $rows);
    }
}
