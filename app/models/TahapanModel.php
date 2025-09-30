<?php
class TahapanModel
{
    public function __construct(private PDO $pdo) {}

    public function all(string $search = ''): array
    {
        if ($search !== '') {
            $sql = "SELECT id_tahapan, nama_tahapan
                    FROM daftar_tahapans
                    WHERE id_tahapan LIKE :q OR nama_tahapan LIKE :q
                    ORDER BY id_tahapan ASC";
            $st  = $this->pdo->prepare($sql);
            $st->execute([':q' => "%{$search}%"]);
        } else {
            $st = $this->pdo->query("SELECT id_tahapan, nama_tahapan
                                     FROM daftar_tahapans
                                     ORDER BY id_tahapan ASC");
        }
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(string $id): ?array
    {
        $st = $this->pdo->prepare("SELECT id_tahapan, nama_tahapan
                                   FROM daftar_tahapans WHERE id_tahapan = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): bool
    {
        // hash tidak relevan di sini; hanya simpan 2 kolom
        $st = $this->pdo->prepare("INSERT INTO daftar_tahapans(id_tahapan, nama_tahapan)
                                   VALUES (:id, :nama)");
        return $st->execute([
            ':id'   => $data['id_tahapan'],
            ':nama' => $data['nama_tahapan'],
        ]);
    }

    public function update(string $id, array $data): bool
    {
        $st = $this->pdo->prepare("UPDATE daftar_tahapans
                                   SET nama_tahapan = :nama
                                   WHERE id_tahapan = :id");
        return $st->execute([':nama' => $data['nama_tahapan'], ':id' => $id]);
    }

    public function delete(string $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM daftar_tahapans WHERE id_tahapan = :id");
        return $st->execute([':id' => $id]);
    }

    /** dipakai untuk validasi unik */
    public function existsId(string $id): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM daftar_tahapans WHERE id_tahapan = :id LIMIT 1");
        $st->execute([':id' => $id]);
        return (bool) $st->fetchColumn();
    }
    public function existsName(string $name): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM daftar_tahapans WHERE nama_tahapan = :n LIMIT 1");
        $st->execute([':n' => $name]);
        return (bool) $st->fetchColumn();
    }
    public function existsNameExcept(string $name, string $exceptId): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM daftar_tahapans
                                   WHERE nama_tahapan = :n AND id_tahapan <> :id LIMIT 1");
        $st->execute([':n' => $name, ':id' => $exceptId]);
        return (bool) $st->fetchColumn();
    }

    /** blokir hapus jika dipakai di jadwal_proyeks */
    public function canDelete(string $id): bool
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM jadwal_proyeks
                                   WHERE daftar_tahapans_id_tahapan = :id");
        $st->execute([':id' => $id]);
        return ((int)$st->fetchColumn()) === 0;
    }

    /** untuk validasi live */
    public function allIds(): array
    {
        return array_map(
            fn($r) => $r['id_tahapan'],
            $this->pdo->query("SELECT id_tahapan FROM daftar_tahapans")->fetchAll(PDO::FETCH_ASSOC) ?: []
        );
    }
    public function allNames(): array
    {
        return array_map(
            fn($r) => $r['nama_tahapan'],
            $this->pdo->query("SELECT nama_tahapan FROM daftar_tahapans")->fetchAll(PDO::FETCH_ASSOC) ?: []
        );
    }
}
