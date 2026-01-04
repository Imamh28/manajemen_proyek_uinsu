<?php
// app/models/ProyekModel.php

require_once __DIR__ . '/PenjadwalanModel.php';

class ProyekModel
{
    public function __construct(private PDO $pdo) {}

    public function all(string $search = ''): array
    {
        $sql = "SELECT p.id_proyek, p.nama_proyek, p.total_biaya_proyek, p.status,
                       k.nama_klien,
                       s.nama_karyawan AS nama_sales,
                       t.nama_karyawan AS nama_site
                  FROM proyek p
             LEFT JOIN klien k     ON p.klien_id_klien = k.id_klien
             LEFT JOIN karyawan s  ON p.karyawan_id_pic_sales = s.id_karyawan
             LEFT JOIN karyawan t  ON p.karyawan_id_pic_site  = t.id_karyawan
                 WHERE 1=1";
        $bind = [];

        if ($search !== '') {
            $sql .= " AND (p.id_proyek LIKE :q
                        OR p.nama_proyek LIKE :q
                        OR k.nama_klien LIKE :q
                        OR s.nama_karyawan LIKE :q
                        OR t.nama_karyawan LIKE :q)";
            $bind[':q'] = "%{$search}%";
        }

        $sql .= " ORDER BY p.id_proyek DESC";
        $st = $this->pdo->prepare($sql);
        $st->execute($bind);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function exportRows(string $search = ''): array
    {
        return $this->all($search);
    }

    public function find(string $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM proyek WHERE id_proyek = :id");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function clients(): array
    {
        $st = $this->pdo->query("SELECT id_klien, nama_klien FROM klien ORDER BY nama_klien ASC");
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * LIST MANDOR utk PIC SITE:
     * - hanya role mandor (RL003)
     * - mandor tidak tampil jika masih pegang proyek yg statusnya belum selesai
     * - currentMandorId disertakan untuk edit (mandor yg sedang dipakai proyek ini tetap muncul)
     */
    public function mandorAvailable(?string $currentMandorId = null, array $doneStatuses = ['Selesai']): array
    {
        $doneStatuses = array_values(array_filter($doneStatuses, fn($s) => $s !== ''));
        if (!$doneStatuses) $doneStatuses = ['Selesai'];

        $ph = [];
        $bind = [];
        foreach ($doneStatuses as $i => $s) {
            $k = ":ds{$i}";
            $ph[] = $k;
            $bind[$k] = $s;
        }

        $sql = "
            SELECT k.id_karyawan, k.nama_karyawan
              FROM karyawan k
             WHERE k.role_id_role = 'RL003'
               AND (
                    k.id_karyawan NOT IN (
                        SELECT p.karyawan_id_pic_site
                          FROM proyek p
                         WHERE p.karyawan_id_pic_site IS NOT NULL
                           AND p.status NOT IN (" . implode(',', $ph) . ")
                    )
                    " . ($currentMandorId ? " OR k.id_karyawan = :cur" : "") . "
               )
             ORDER BY k.nama_karyawan ASC
        ";

        if ($currentMandorId) $bind[':cur'] = $currentMandorId;

        $st = $this->pdo->prepare($sql);
        $st->execute($bind);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function existingIds(): array
    {
        $st = $this->pdo->query("SELECT id_proyek FROM proyek");
        return array_map(fn($r) => $r['id_proyek'], $st->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public function existingNames(): array
    {
        $st = $this->pdo->query("SELECT nama_proyek FROM proyek");
        return array_map(fn($r) => $r['nama_proyek'], $st->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public function existsId(string $id): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM proyek WHERE id_proyek = :id LIMIT 1");
        $st->execute([':id' => $id]);
        return (bool)$st->fetchColumn();
    }

    public function existsName(string $nama, ?string $exceptId = null): bool
    {
        $sql = "SELECT 1 FROM proyek WHERE nama_proyek = :n";
        $bind = [':n' => $nama];
        if ($exceptId) {
            $sql .= " AND id_proyek <> :id";
            $bind[':id'] = $exceptId;
        }
        $sql .= " LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute($bind);
        return (bool)$st->fetchColumn();
    }

    public function generateQuotationCode(): string
    {
        $prefix = 'QUO';
        $ym = date('Ym');
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM proyek WHERE quotation LIKE :p");
        $st->execute([':p' => "{$prefix}{$ym}%"]);
        $n = (int)$st->fetchColumn() + 1;
        return $prefix . $ym . str_pad((string)$n, 3, '0', STR_PAD_LEFT);
    }

    public function create(array $d): bool
    {
        $sql = "INSERT INTO proyek
                (id_proyek, nama_proyek, deskripsi, total_biaya_proyek, alamat,
                 tanggal_mulai, tanggal_selesai, status, klien_id_klien,
                 karyawan_id_pic_sales, karyawan_id_pic_site, quotation, gambar_kerja)
                VALUES
                (:id_proyek, :nama_proyek, :deskripsi, :total_biaya_proyek, :alamat,
                 :tanggal_mulai, :tanggal_selesai, :status, :klien_id_klien,
                 :karyawan_id_pic_sales, :karyawan_id_pic_site, :quotation, :gambar_kerja)";
        $st = $this->pdo->prepare($sql);
        return $st->execute([
            ':id_proyek'             => $d['id_proyek'],
            ':nama_proyek'           => $d['nama_proyek'],
            ':deskripsi'             => $d['deskripsi'],
            ':total_biaya_proyek'    => (int)$d['total_biaya_proyek'],
            ':alamat'                => $d['alamat'],
            ':tanggal_mulai'         => $d['tanggal_mulai'],
            ':tanggal_selesai'       => $d['tanggal_selesai'],
            ':status'                => $d['status'],
            ':klien_id_klien'        => $d['klien_id_klien'],
            ':karyawan_id_pic_sales' => $d['karyawan_id_pic_sales'],
            ':karyawan_id_pic_site'  => $d['karyawan_id_pic_site'],
            ':quotation'             => $d['quotation'],
            ':gambar_kerja'          => $d['gambar_kerja'],
        ]);
    }

    public function update(string $id, array $d): bool
    {
        $sql = "UPDATE proyek SET
                    nama_proyek = :nama_proyek,
                    deskripsi = :deskripsi,
                    total_biaya_proyek = :total_biaya_proyek,
                    alamat = :alamat,
                    tanggal_mulai = :tanggal_mulai,
                    tanggal_selesai = :tanggal_selesai,
                    status = :status,
                    klien_id_klien = :klien_id_klien,
                    karyawan_id_pic_site = :karyawan_id_pic_site,
                    gambar_kerja = :gambar_kerja
                WHERE id_proyek = :id";
        $st = $this->pdo->prepare($sql);
        return $st->execute([
            ':nama_proyek'          => $d['nama_proyek'],
            ':deskripsi'            => $d['deskripsi'],
            ':total_biaya_proyek'   => (int)$d['total_biaya_proyek'],
            ':alamat'               => $d['alamat'],
            ':tanggal_mulai'        => $d['tanggal_mulai'],
            ':tanggal_selesai'      => $d['tanggal_selesai'],
            ':status'               => $d['status'],
            ':klien_id_klien'       => $d['klien_id_klien'],
            ':karyawan_id_pic_site' => $d['karyawan_id_pic_site'],
            ':gambar_kerja'         => $d['gambar_kerja'],
            ':id'                   => $id,
        ]);
    }

    public function delete(string $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM proyek WHERE id_proyek = :id");
        return $st->execute([':id' => $id]);
    }

    public function canDelete(string $id): bool
    {
        $tables = [
            "jadwal_proyeks" => "SELECT 1 FROM jadwal_proyeks WHERE proyek_id_proyek = :id LIMIT 1",
            "pembayarans"    => "SELECT 1 FROM pembayarans WHERE proyek_id_proyek = :id LIMIT 1",
        ];

        foreach ($tables as $sql) {
            $st = $this->pdo->prepare($sql);
            $st->execute([':id' => $id]);
            if ($st->fetchColumn()) return false;
        }

        try {
            $st = $this->pdo->prepare("SELECT 1 FROM tahapan_update_requests WHERE proyek_id_proyek = :id LIMIT 1");
            $st->execute([':id' => $id]);
            if ($st->fetchColumn()) return false;
        } catch (Throwable $e) {
            // abaikan jika tabel tidak ada
        }

        return true;
    }

    public function setStatus(string $id, string $status): bool
    {
        $st = $this->pdo->prepare("UPDATE proyek SET status = :s WHERE id_proyek = :id");
        return $st->execute([':s' => $status, ':id' => $id]);
    }

    /** panggil ini dari controller progres/tahapan saat pertama kali ada progres berjalan. */
    public function ensureStarted(string $id): void
    {
        $st = $this->pdo->prepare("UPDATE proyek SET status='Berjalan' WHERE id_proyek=:id AND status='Menunggu'");
        $st->execute([':id' => $id]);
    }

    /** Validasi PIC Sales: hanya PIC Sales proyek ini yang boleh input pembayaran */
    public function isPicSales(string $idProyek, string $idKaryawan): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM proyek WHERE id_proyek=:id AND karyawan_id_pic_sales=:k LIMIT 1");
        $st->execute([':id' => $idProyek, ':k' => $idKaryawan]);
        return (bool)$st->fetchColumn();
    }

    /**
     * ✅ FINAL: Sinkronisasi status + current_tahapan_id proyek berdasarkan data relasi yang tersisa.
     * Panggil ini SETIAP KALI ada perubahan relasi: jadwal_proyeks / tahapan_update_requests.
     *
     * Aturan utama:
     * - Jika TIDAK ADA jadwal sama sekali -> proyek jadi Menunggu & current_tahapan_id = NULL
     *   (ini memenuhi logika Anda saat semua jadwal & semua request sudah dihapus)
     * - Jika ADA jadwal:
     *   - status Berjalan jika ada jadwal mulai terisi, selain itu Menunggu
     *   - current_tahapan_id = tahapan pertama yang belum selesai
     *   - jika semua jadwal selesai, current_tahapan_id = next tahapan master yang belum dijadwalkan (jika ada), else NULL
     *   - jika semua tahapan master sudah dijadwalkan & semua jadwal selesai -> status Selesai & current_tahapan_id NULL
     * - Jika proyek LOCKED (Selesai/Dibatalkan): tidak mengubah status, tetapi current_tahapan_id dipaksa NULL.
     *
     * Catatan:
     * - tahapan_update_requests tidak dijadikan sumber status utama (status utama = jadwal_proyeks),
     *   tapi tetap dihitung untuk membantu mendeteksi kondisi orphan/mismatch bila diperlukan.
     */
    public function syncStateAfterRelationsChange(string $idProyek, array $lockedStatuses = ['Selesai', 'Dibatalkan']): void
    {
        $idProyek = strtoupper(trim($idProyek));
        if ($idProyek === '') return;

        // ambil status sekarang
        $st0 = $this->pdo->prepare("SELECT status FROM proyek WHERE id_proyek = :p LIMIT 1");
        $st0->execute([':p' => $idProyek]);
        $curStatus = (string)($st0->fetchColumn() ?: '');
        if ($curStatus === '') return;

        // kalau proyek locked -> jangan utak-atik status, tapi current harus NULL
        if (in_array($curStatus, $lockedStatuses, true)) {
            $up = $this->pdo->prepare("UPDATE proyek SET current_tahapan_id = NULL WHERE id_proyek = :p");
            $up->execute([':p' => $idProyek]);
            return;
        }

        // hitung jadwal yang tersisa
        $stJ = $this->pdo->prepare("SELECT COUNT(*) FROM jadwal_proyeks WHERE proyek_id_proyek = :p");
        $stJ->execute([':p' => $idProyek]);
        $jadwalCount = (int)($stJ->fetchColumn() ?? 0);

        // hitung requests (untuk deteksi mismatch / orphan)
        $reqCount = 0;
        try {
            $stR = $this->pdo->prepare("SELECT COUNT(*) FROM tahapan_update_requests WHERE proyek_id_proyek = :p");
            $stR->execute([':p' => $idProyek]);
            $reqCount = (int)($stR->fetchColumn() ?? 0);
        } catch (Throwable $e) {
            $reqCount = 0;
        }

        // ✅ KUNCI LOGIKA ANDA:
        // Jika semua jadwal hilang, proyek WAJIB kembali Menunggu & current NULL.
        // (Walaupun request masih tersisa/orphan, status proyek tetap harus reset)
        if ($jadwalCount === 0) {
            $up0 = $this->pdo->prepare("
                UPDATE proyek
                   SET status = 'Menunggu',
                       current_tahapan_id = NULL
                 WHERE id_proyek = :p
            ");
            $up0->execute([':p' => $idProyek]);
            return;
        }

        // status dasar: berjalan jika ada jadwal yang sudah mulai
        $stS = $this->pdo->prepare("
            SELECT 1
              FROM jadwal_proyeks
             WHERE proyek_id_proyek = :p
               AND mulai IS NOT NULL AND mulai <> ''
             LIMIT 1
        ");
        $stS->execute([':p' => $idProyek]);
        $hasStarted = (bool)$stS->fetchColumn();

        $newStatus = $hasStarted ? 'Berjalan' : 'Menunggu';

        // cek apakah semua selesai & seluruh master sudah dijadwalkan
        $masterCnt = (int)($this->pdo->query("SELECT COUNT(*) FROM daftar_tahapans")->fetchColumn() ?? 0);

        $stSched = $this->pdo->prepare("
            SELECT COUNT(DISTINCT daftar_tahapans_id_tahapan)
              FROM jadwal_proyeks
             WHERE proyek_id_proyek = :p
        ");
        $stSched->execute([':p' => $idProyek]);
        $schedCnt = (int)($stSched->fetchColumn() ?? 0);

        $stUnf = $this->pdo->prepare("
            SELECT COUNT(*)
              FROM jadwal_proyeks
             WHERE proyek_id_proyek = :p
               AND (selesai IS NULL OR selesai = '')
        ");
        $stUnf->execute([':p' => $idProyek]);
        $unfinishedCnt = (int)($stUnf->fetchColumn() ?? 0);

        if ($masterCnt > 0 && $schedCnt === $masterCnt && $unfinishedCnt === 0) {
            $newStatus = 'Selesai';
        }

        // current tahapan:
        $newCur = null;

        if ($newStatus === 'Selesai') {
            $newCur = null;
        } else {
            // ambil tahapan pertama yang belum selesai (ini juga mencakup "belum mulai")
            $stCur = $this->pdo->prepare("
                SELECT daftar_tahapans_id_tahapan
                  FROM jadwal_proyeks
                 WHERE proyek_id_proyek = :p
                   AND (selesai IS NULL OR selesai = '')
                 ORDER BY daftar_tahapans_id_tahapan ASC, id_jadwal ASC
                 LIMIT 1
            ");
            $stCur->execute([':p' => $idProyek]);
            $cur = (string)($stCur->fetchColumn() ?: '');

            if ($cur !== '') {
                $newCur = $cur;
            } else {
                // semua jadwal selesai, tapi bisa saja masih ada master yang belum dijadwalkan
                $pm = new PenjadwalanModel($this->pdo);
                $next = $pm->nextTahapanId($idProyek);
                $newCur = $next ?: null;
            }
        }

        $up = $this->pdo->prepare("
            UPDATE proyek
               SET status = :s,
                   current_tahapan_id = :t
             WHERE id_proyek = :p
        ");
        $up->execute([
            ':s' => $newStatus,
            ':t' => $newCur,
            ':p' => $idProyek,
        ]);
    }

    // ==== DELETE GUARD + CASCADE (manual) ====

    public function relationsSummary(string $id): array
    {
        $out = [
            'jadwal' => 0,
            'pembayaran' => 0,
            'tahapan_requests' => 0,
        ];

        // jadwal_proyeks
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM jadwal_proyeks WHERE proyek_id_proyek = :id");
        $st->execute([':id' => $id]);
        $out['jadwal'] = (int)($st->fetchColumn() ?? 0);

        // pembayarans
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM pembayarans WHERE proyek_id_proyek = :id");
        $st->execute([':id' => $id]);
        $out['pembayaran'] = (int)($st->fetchColumn() ?? 0);

        // tahapan_update_requests
        try {
            $st = $this->pdo->prepare("SELECT COUNT(*) FROM tahapan_update_requests WHERE proyek_id_proyek = :id");
            $st->execute([':id' => $id]);
            $out['tahapan_requests'] = (int)($st->fetchColumn() ?? 0);
        } catch (Throwable $e) {
            $out['tahapan_requests'] = 0;
        }

        return $out;
    }

    public function hasRelations(string $id): bool
    {
        $r = $this->relationsSummary($id);
        return ($r['jadwal'] + $r['pembayaran'] + $r['tahapan_requests']) > 0;
    }

    /**
     * Force delete proyek + relasi (manual cascade).
     * $opt: ['jadwal'=>bool,'pembayaran'=>bool,'tahapan_requests'=>bool]
     */
    public function deleteCascade(string $id, array $opt): bool
    {
        $delJ = !empty($opt['jadwal']);
        $delP = !empty($opt['pembayaran']);
        $delT = !empty($opt['tahapan_requests']);

        $this->pdo->beginTransaction();
        try {
            if ($delJ) {
                $st = $this->pdo->prepare("DELETE FROM jadwal_proyeks WHERE proyek_id_proyek = :id");
                $st->execute([':id' => $id]);
            }

            if ($delP) {
                $st = $this->pdo->prepare("DELETE FROM pembayarans WHERE proyek_id_proyek = :id");
                $st->execute([':id' => $id]);
            }

            if ($delT) {
                try {
                    $st = $this->pdo->prepare("DELETE FROM tahapan_update_requests WHERE proyek_id_proyek = :id");
                    $st->execute([':id' => $id]);
                } catch (Throwable $e) {
                    // ignore jika tabel tidak ada
                }
            }

            // terakhir: proyek
            $st = $this->pdo->prepare("DELETE FROM proyek WHERE id_proyek = :id");
            $ok = $st->execute([':id' => $id]);

            $this->pdo->commit();
            return $ok;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
}
