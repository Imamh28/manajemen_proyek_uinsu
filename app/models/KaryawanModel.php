<?php

class KaryawanModel
{
    public function __construct(private PDO $pdo) {}

    /** Ambil semua karyawan + nama role (dengan pencarian opsional) */
    public function all(string $keyword = ''): array
    {
        $sql = "
            SELECT k.id_karyawan, k.nama_karyawan, k.no_telepon_karyawan,
                   k.email, k.role_id_role, r.nama_role
            FROM karyawan k
            JOIN role r ON r.id_role = k.role_id_role
        ";
        $params = [];
        if ($keyword !== '') {
            $sql .= " WHERE (k.id_karyawan LIKE :kw
                        OR k.nama_karyawan LIKE :kw
                        OR k.email LIKE :kw
                        OR k.no_telepon_karyawan LIKE :kw)";
            $params[':kw'] = "%{$keyword}%";
        }
        $sql .= " ORDER BY k.id_karyawan ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Ambil semua role untuk dropdown */
    public function roles(): array
    {
        $st = $this->pdo->query("SELECT id_role, nama_role FROM role ORDER BY nama_role ASC");
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Tambah karyawan (password sementara disimpan plaintext agar cocok dgn login saat ini) */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO karyawan
                (id_karyawan, nama_karyawan, no_telepon_karyawan, email, password, role_id_role)
                VALUES (:id, :nama, :telp, :email, :pass, :role)";
        $st = $this->pdo->prepare($sql);
        return $st->execute([
            ':id'    => $data['id_karyawan'],
            ':nama'  => $data['nama_karyawan'],
            ':telp'  => $data['no_telepon_karyawan'],
            ':email' => $data['email'],
            ':pass'  => password_hash($data['password'], PASSWORD_DEFAULT), // TODO: ganti ke password_hash + password_verify setelah Auth diperbarui
            ':role'  => $data['role_id_role'],
        ]);
    }

    /** Cari satu karyawan */
    public function find(string $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM karyawan WHERE id_karyawan = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Update karyawan (jika password kosong, tidak diubah) */
    public function update(string $id, array $data): bool
    {
        if (!empty($data['password'])) {
            $sql = "UPDATE karyawan
                    SET nama_karyawan = :nama,
                        no_telepon_karyawan = :telp,
                        email = :email,
                        password = :pass,
                        role_id_role = :role
                    WHERE id_karyawan = :id";
            $params = [
                ':nama'  => $data['nama_karyawan'],
                ':telp'  => $data['no_telepon_karyawan'],
                ':email' => $data['email'],
                ':pass'  => password_hash($data['password'], PASSWORD_DEFAULT), // TODO: switch to hash
                ':role'  => $data['role_id_role'],
                ':id'    => $id,
            ];
        } else {
            $sql = "UPDATE karyawan
                    SET nama_karyawan = :nama,
                        no_telepon_karyawan = :telp,
                        email = :email,
                        role_id_role = :role
                    WHERE id_karyawan = :id";
            $params = [
                ':nama'  => $data['nama_karyawan'],
                ':telp'  => $data['no_telepon_karyawan'],
                ':email' => $data['email'],
                ':role'  => $data['role_id_role'],
                ':id'    => $id,
            ];
        }
        $st = $this->pdo->prepare($sql);
        return $st->execute($params);
    }

    /** Boleh hapus? Pastikan tidak sedang dipakai di proyek sebagai PIC */
    public function canDelete(string $id): bool
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM proyek WHERE karyawan_id_pic_sales = :id) +
                    (SELECT COUNT(*) FROM proyek WHERE karyawan_id_pic_site  = :id) AS total";
        $st = $this->pdo->prepare($sql);
        $st->execute([':id' => $id]);
        $total = (int)($st->fetchColumn() ?: 0);
        return $total === 0;
    }

    /** Hapus karyawan */
    public function delete(string $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM karyawan WHERE id_karyawan = :id");
        return $st->execute([':id' => $id]);
    }

    public function existsId(string $id): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM karyawan WHERE id_karyawan = :id LIMIT 1");
        $st->execute([':id' => $id]);
        return (bool)$st->fetchColumn();
    }

    public function existsEmail(string $email): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM karyawan WHERE email = :e LIMIT 1");
        $st->execute([':e' => $email]);
        return (bool)$st->fetchColumn();
    }

    public function existsPhone(string $phone): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM karyawan WHERE no_telepon_karyawan = :p LIMIT 1");
        $st->execute([':p' => $phone]);
        return (bool)$st->fetchColumn();
    }

    public function existsEmailExcept(string $email, string $exceptId): bool
    {
        $st = $this->pdo->prepare(
            "SELECT 1 FROM karyawan WHERE email = :e AND id_karyawan <> :id LIMIT 1"
        );
        $st->execute([':e' => $email, ':id' => $exceptId]);
        return (bool)$st->fetchColumn();
    }

    public function existsPhoneExcept(string $phone, string $exceptId): bool
    {
        $st = $this->pdo->prepare(
            "SELECT 1 FROM karyawan WHERE no_telepon_karyawan = :p AND id_karyawan <> :id LIMIT 1"
        );
        $st->execute([':p' => $phone, ':id' => $exceptId]);
        return (bool)$st->fetchColumn();
    }
}
