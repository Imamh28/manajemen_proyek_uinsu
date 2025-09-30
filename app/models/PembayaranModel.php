<?php
// app/models/PembayaranModel.php
class PembayaranModel
{
    public function __construct(private PDO $pdo) {}

    public function all(string $q = ''): array
    {
        $sql = "SELECT pb.*, pr.nama_proyek
                  FROM pembayarans pb
             LEFT JOIN proyek pr ON pb.proyek_id_proyek = pr.id_proyek
                 WHERE 1=1";
        $bind = [];
        if ($q !== '') {
            $sql .= " AND (pb.id_pem_bayaran LIKE :q
                       OR pr.nama_proyek LIKE :q
                       OR pb.status_pembayaran LIKE :q)";
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
            ':id'    => $d['id_pem_bayaran'],
            ':jenis' => $d['jenis_pembayaran'],
            ':sub'   => $d['sub_total'],
            ':pajak' => $d['pajak_pembayaran'],
            ':total' => $d['total_pembayaran'],
            ':jt'    => $d['tanggal_jatuh_tempo'],
            ':tb'    => $d['tanggal_bayar'],
            ':status' => $d['status_pembayaran'],
            ':bukti' => $d['bukti_pembayaran'],
            ':prj'   => $d['proyek_id_proyek'],
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
            ':id'    => $id,
            ':jenis' => $d['jenis_pembayaran'],
            ':sub'   => $d['sub_total'],
            ':pajak' => $d['pajak_pembayaran'],
            ':total' => $d['total_pembayaran'],
            ':jt'    => $d['tanggal_jatuh_tempo'],
            ':tb'    => $d['tanggal_bayar'],
            ':status' => $d['status_pembayaran'],
            ':bukti' => $d['bukti_pembayaran'],
            ':prj'   => $d['proyek_id_proyek'],
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
        return $this->pdo->query("SELECT id_pem_bayaran FROM pembayarans")->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public function projects(): array
    {
        return $this->pdo->query("SELECT id_proyek, nama_proyek FROM proyek ORDER BY id_proyek DESC")
            ->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // export tampilan rapi
    public function exportRows(string $q = ''): array
    {
        $rows = $this->all($q);
        return array_map(function ($r) {
            return [
                'ID'            => $r['id_pem_bayaran'],
                'Proyek'        => $r['nama_proyek'],
                'Jenis'         => $r['jenis_pembayaran'],
                'Sub Total'     => $r['sub_total'],
                'Pajak'         => $r['pajak_pembayaran'],
                'Total'         => $r['total_pembayaran'],
                'Jatuh Tempo'   => $r['tanggal_jatuh_tempo'],
                'Tanggal Bayar' => $r['tanggal_bayar'],
                'Status'        => $r['status_pembayaran'],
            ];
        }, $rows);
    }
}
