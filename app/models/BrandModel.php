<?php
class BrandModel
{
    public function __construct(private PDO $pdo) {}

    /** List brand (dengan pencarian opsional) */
    public function all(string $search = ''): array
    {
        if ($search !== '') {
            $sql = "SELECT id_brand, nama_brand
                    FROM brand
                    WHERE id_brand LIKE :q OR nama_brand LIKE :q
                    ORDER BY id_brand ASC";
            $st  = $this->pdo->prepare($sql);
            $st->execute([':q' => "%{$search}%"]);
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        $sql = "SELECT id_brand, nama_brand FROM brand ORDER BY id_brand ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(int $id): ?array
    {
        $st = $this->pdo->prepare("SELECT id_brand, nama_brand FROM brand WHERE id_brand = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $st = $this->pdo->prepare("INSERT INTO brand (nama_brand) VALUES (:n)");
        return $st->execute([':n' => $data['nama_brand']]);
    }

    public function update(int $id, array $data): bool
    {
        $st = $this->pdo->prepare("UPDATE brand SET nama_brand = :n WHERE id_brand = :id");
        return $st->execute([':n' => $data['nama_brand'], ':id' => $id]);
    }

    public function delete(int $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM brand WHERE id_brand = :id");
        return $st->execute([':id' => $id]);
    }

    /** Unik nama (case-insensitive) */
    public function existsName(string $name): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM brand WHERE LOWER(nama_brand)=LOWER(:n) LIMIT 1");
        $st->execute([':n' => $name]);
        return (bool)$st->fetchColumn();
    }
    public function existsNameExcept(string $name, int $exceptId): bool
    {
        $st = $this->pdo->prepare(
            "SELECT 1 FROM brand WHERE LOWER(nama_brand)=LOWER(:n) AND id_brand <> :id LIMIT 1"
        );
        $st->execute([':n' => $name, ':id' => $exceptId]);
        return (bool)$st->fetchColumn();
    }

    /** Cegah hapus bila dipakai di proyek */
    public function canDelete(int $id): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM proyek WHERE brand_id_brand = :id LIMIT 1");
        $st->execute([':id' => $id]);
        return !$st->fetchColumn();
    }

    /** Untuk live validation unik di client */
    public function names(): array
    {
        $rows = $this->pdo->query("SELECT nama_brand FROM brand")->fetchAll(PDO::FETCH_COLUMN) ?: [];
        return array_values($rows);
    }
}
