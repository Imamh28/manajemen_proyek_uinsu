<?php

class UserModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Ambil user + nama role
    public function findByEmailWithRole(string $email): ?array
    {
        $sql = "
            SELECT k.id_karyawan, k.nama_karyawan, k.email, k.password,
                   k.role_id_role, r.nama_role
            FROM karyawan k
            JOIN role r ON r.id_role = k.role_id_role
            WHERE k.email = :email
            LIMIT 1
        ";
        $st = $this->pdo->prepare($sql);
        $st->execute(['email' => $email]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Ambil daftar menu yang boleh untuk role tertentu
    public function getMenusByRole(string $roleId): array
    {
        $sql = "
            SELECT m.id_menu, m.nama_menu, m.url, m.kategori_menus_id_kategori_menus AS cat_id
            FROM role_menu rm
            JOIN menus m ON m.id_menu = rm.menus_id_menu
            WHERE rm.role_id_role = :role
            ORDER BY m.kategori_menus_id_kategori_menus, m.id_menu
        ";
        $st = $this->pdo->prepare($sql);
        $st->execute(['role' => $roleId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // Ambil nama kategori untuk sekumpulan cat_id (untuk label sidebar)
    public function getCategoryNamesByIds(array $ids): array
    {
        if (empty($ids)) return [];
        $in  = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT id_kategori_menus AS id, nama_kategori_menu AS name
                FROM kategori_menus WHERE id_kategori_menus IN ($in)";
        $st  = $this->pdo->prepare($sql);
        $st->execute($ids);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $map = [];
        foreach ($rows as $r) $map[(int)$r['id']] = $r['name'];
        return $map;
    }

    // Cek apakah role boleh akses URL tertentu
    public function roleCanAccessUrl(string $roleId, string $url): bool
    {
        $sql = "
            SELECT 1
            FROM role_menu rm
            JOIN menus m ON m.id_menu = rm.menus_id_menu
            WHERE rm.role_id_role = :role AND m.url = :url
            LIMIT 1
        ";
        $st = $this->pdo->prepare($sql);
        $st->execute(['role' => $roleId, 'url' => $url]);
        return (bool)$st->fetchColumn();
    }

    public function updatePasswordHash(string $idKaryawan, string $newHash): bool
    {
        $st = $this->pdo->prepare("UPDATE karyawan SET password = :h WHERE id_karyawan = :id");
        return $st->execute([':h' => $newHash, ':id' => $idKaryawan]);
    }
}
