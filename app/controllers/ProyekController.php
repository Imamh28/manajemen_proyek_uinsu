<?php
// app/controllers/ProyekController.php
require_once __DIR__ . '/../models/ProyekModel.php';
require_once __DIR__ . '/../utils/csrf.php';
require_once __DIR__ . '/../helpers/Audit.php';
require_once __DIR__ . '/../helpers/Notify.php';

class ProyekController
{
    private ProyekModel $model;
    private array $statusEnum = ['Menunggu', 'Berjalan', 'Selesai', 'Dibatalkan'];

    public function __construct(private PDO $pdo, private string $baseUrl)
    {
        $this->model   = new ProyekModel($pdo);
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    /** GET /proyek : list + form */
    public function index(): void
    {
        // HANYA ADMIN
        require_roles(['RL001'], $this->baseUrl);

        $search       = trim($_GET['search'] ?? '');
        $rows         = $this->model->all($search);
        $klienList    = $this->model->clients();
        $brandList    = $this->model->brands();
        $karyawanList = $this->model->employees();

        // untuk view
        $BASE_URL     = $this->baseUrl;
        $proyek       = $rows;
        $statusEnum   = $this->statusEnum;

        // daftar unik utk live validate
        $EXISTING_IDS_JSON   = json_encode($this->model->existingIds(),   JSON_UNESCAPED_UNICODE);
        $EXISTING_NAMES_JSON = json_encode($this->model->existingNames(), JSON_UNESCAPED_UNICODE);

        // quotation berikutnya (readonly di form; server tetap generate ulang saat store)
        $NEXT_QUOTATION = $this->model->generateQuotationCode();

        include __DIR__ . '/../views/admin/proyek/main.php';
    }

    /** POST /proyek/store */
    public function store(): void
    {
        // HANYA ADMIN
        require_roles(['RL001'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=proyek");
            exit;
        }

        // ambil input (uppercase ID untuk konsistensi)
        $d = [
            'id_proyek'              => strtoupper(trim($_POST['id_proyek'] ?? '')),
            'nama_proyek'            => trim($_POST['nama_proyek'] ?? ''),
            'deskripsi'              => trim($_POST['deskripsi'] ?? ''),
            'total_biaya_proyek'     => trim($_POST['total_biaya_proyek'] ?? ''),
            'alamat'                 => trim($_POST['alamat'] ?? ''),
            'tanggal_mulai'          => trim($_POST['tanggal_mulai'] ?? ''),
            'tanggal_selesai'        => trim($_POST['tanggal_selesai'] ?? ''),
            'status'                 => trim($_POST['status'] ?? ''),
            'klien_id_klien'         => trim($_POST['klien_id_klien'] ?? ''),
            'brand_id_brand'         => trim($_POST['brand_id_brand'] ?? ''),
            'karyawan_id_pic_sales'  => trim($_POST['karyawan_id_pic_sales'] ?? ''),
            'karyawan_id_pic_site'   => trim($_POST['karyawan_id_pic_site'] ?? ''),
            // server yang menentukan:
            'quotation'              => $this->model->generateQuotationCode(),
            'gambar_kerja'           => null,
        ];

        // validasi common
        $err = $this->validate($d, null);

        // unik
        if ($this->model->existsId($d['id_proyek']))      $err['id_proyek']   = 'ID proyek sudah digunakan.';
        if ($this->model->existsName($d['nama_proyek']))  $err['nama_proyek'] = 'Nama proyek sudah digunakan.';

        // upload (opsional)
        $upload = $this->handleUpload('gambar_kerja');
        if (isset($upload['error'])) {
            $err['gambar_kerja'] = $upload['error'];
        } else {
            $d['gambar_kerja'] = $upload['path'] ?? null;
        }

        if ($err) {
            $_SESSION['form_errors']['proyek_store'] = $err;
            $_SESSION['form_old']['proyek_store']    = $d;
            header("Location: {$this->baseUrl}index.php?r=proyek");
            exit;
        }

        try {
            if ($this->model->create($d)) {
                audit_log('proyek.store', ['id' => $d['id_proyek'], 'nama' => $d['nama_proyek'], 'quo' => $d['quotation']]);
                $_SESSION['success'] = 'Proyek berhasil ditambahkan.';
                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'proyek_created', [
                    'proyek_id' => $d['id_proyek'],
                    'actor_id'  => $actorId,
                ]);
            } else {
                $_SESSION['error'] = 'Gagal menambah proyek.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menambah proyek: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=proyek");
        exit;
    }

    /** GET /proyek/edit&id=PRJ001 */
    public function editForm(): void
    {
        // HANYA ADMIN
        require_roles(['RL001'], $this->baseUrl);

        $id = trim($_GET['id'] ?? '');
        if ($id === '') {
            $_SESSION['error'] = 'ID tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=proyek");
            exit;
        }

        $row = $this->model->find($id);
        if (!$row) {
            $_SESSION['error'] = 'Data tidak ditemukan.';
            header("Location: {$this->baseUrl}index.php?r=proyek");
            exit;
        }

        $klienList    = $this->model->clients();
        $brandList    = $this->model->brands();
        $karyawanList = $this->model->employees();

        $BASE_URL     = $this->baseUrl;
        $statusEnum   = $this->statusEnum;
        $proyek       = $row;

        // live-unique nama (abaikan current)
        $EXISTING_NAMES_JSON = json_encode($this->model->existingNames(), JSON_UNESCAPED_UNICODE);
        $CURRENT_NAME        = $row['nama_proyek'];

        include __DIR__ . '/../views/admin/proyek/edit.php';
    }

    /** POST /proyek/update */
    public function update(): void
    {
        // HANYA ADMIN
        require_roles(['RL001'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=proyek");
            exit;
        }

        $id = trim($_POST['id_proyek'] ?? '');
        if ($id === '') {
            $_SESSION['error'] = 'ID tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=proyek");
            exit;
        }

        // yang boleh diubah
        $d = [
            'nama_proyek'            => trim($_POST['nama_proyek'] ?? ''),
            'deskripsi'              => trim($_POST['deskripsi'] ?? ''),
            'total_biaya_proyek'     => trim($_POST['total_biaya_proyek'] ?? ''),
            'alamat'                 => trim($_POST['alamat'] ?? ''),
            'tanggal_mulai'          => trim($_POST['tanggal_mulai'] ?? ''),
            'tanggal_selesai'        => trim($_POST['tanggal_selesai'] ?? ''),
            'status'                 => trim($_POST['status'] ?? ''),
            'klien_id_klien'         => trim($_POST['klien_id_klien'] ?? ''),
            'brand_id_brand'         => trim($_POST['brand_id_brand'] ?? ''),
            'karyawan_id_pic_sales'  => trim($_POST['karyawan_id_pic_sales'] ?? ''),
            'karyawan_id_pic_site'   => trim($_POST['karyawan_id_pic_site'] ?? ''),
            'gambar_kerja'           => null,
        ];

        $err = $this->validate($d, $id);

        // unik nama (kecuali dirinya sendiri)
        if ($this->model->existsName($d['nama_proyek'], $id)) {
            $err['nama_proyek'] = 'Nama proyek sudah digunakan.';
        }

        // upload (opsional) – jika ada file baru, replace path
        $current = $this->model->find($id);
        $upload  = $this->handleUpload('gambar_kerja', true);
        if (isset($upload['error'])) {
            $err['gambar_kerja'] = $upload['error'];
        } else {
            $d['gambar_kerja'] = $upload['path'] ?? ($current['gambar_kerja'] ?? null);
        }

        if ($err) {
            $_SESSION['form_errors']['proyek_update'] = $err;
            $_SESSION['form_old']['proyek_update']    = $d;
            header("Location: {$this->baseUrl}index.php?r=proyek/edit&id=" . urlencode($id));
            exit;
        }

        try {
            if ($this->model->update($id, $d)) {
                audit_log('proyek.update', ['id' => $id, 'nama' => $d['nama_proyek']]);
                $_SESSION['success'] = 'Proyek berhasil diperbarui.';
                // === NOTIFIKASI: Proyek diubah ===
                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'proyek_updated', [
                    'proyek_id' => $id,
                    'actor_id'  => $actorId,
                ]);
            } else {
                $_SESSION['error'] = 'Tidak ada perubahan atau update gagal.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal memperbarui proyek: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=proyek");
        exit;
    }

    /** POST /proyek/delete */
    public function delete(): void
    {
        // HANYA ADMIN
        require_roles(['RL001'], $this->baseUrl);

        $token = $_POST['_csrf'] ?? '';
        if (!csrf_verify($token)) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=proyek");
            exit;
        }

        $id = trim($_POST['hapus_id'] ?? '');
        if ($id === '') {
            $_SESSION['error'] = 'ID tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=proyek");
            exit;
        }

        try {
            if (!$this->model->canDelete($id)) {
                $_SESSION['error'] = 'Proyek memiliki relasi (jadwal/pembayaran). Tidak dapat dihapus.';
            } else {
                if ($this->model->delete($id)) {
                    audit_log('proyek.delete', ['id' => $id]);
                    $_SESSION['success'] = 'Proyek berhasil dihapus.';
                    // === NOTIFIKASI: Proyek dihapus ===
                    $actorId = (string)($_SESSION['user']['id'] ?? '');
                    notif_event($this->pdo, 'proyek_deleted', [
                        'proyek_id' => $id,
                        'actor_id'  => $actorId,
                    ]);
                } else {
                    $_SESSION['error'] = 'Gagal menghapus proyek.';
                }
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menghapus proyek: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=proyek");
        exit;
    }

    /** GET /proyek/export */
    public function exportCSV(): void
    {
        // HANYA ADMIN
        require_roles(['RL001'], $this->baseUrl);

        $search = trim($_GET['search'] ?? '');
        $rows   = $this->model->exportRows($search);

        $filename = 'proyek-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache');

        $out = fopen('php://output', 'w');
        // BOM untuk Excel
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, ['ID', 'Nama Proyek', 'Klien', 'Brand', 'Total Biaya', 'Status', 'PIC Sales', 'PIC Site']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id_proyek'],
                $r['nama_proyek'],
                $r['nama_klien'],
                $r['nama_brand'],
                $r['total_biaya_proyek'],
                $r['status'],
                $r['nama_sales'],
                $r['nama_site'],
            ]);
        }
        fclose($out);

        audit_log('proyek.export', ['count' => count($rows), 'search' => $search]);
        exit;
    }

    // ====== validation rules ======
    private function validate(array &$d, ?string $id = null): array
    {
        $errors = [];

        // required semuanya (kecuali gambar_kerja opsional)
        $required = [
            'id' => $id ? null : 'id_proyek', // saat update, ID dari hidden
            'nama_proyek',
            'deskripsi',
            'total_biaya_proyek',
            'alamat',
            'tanggal_mulai',
            'tanggal_selesai',
            'status',
            'klien_id_klien',
            'brand_id_brand',
            'karyawan_id_pic_sales',
            'karyawan_id_pic_site'
        ];
        foreach ($required as $k) {
            if ($k === null) continue;
            if (($d[$k] ?? '') === '') $errors[$k] = 'Wajib diisi.';
        }

        // ID prefix PRJ (saat create)
        if (!$id && ($d['id_proyek'] ?? '') !== '' && !str_starts_with($d['id_proyek'], 'PRJ')) {
            $errors['id_proyek'] = 'ID harus diawali "PRJ".';
        }

        // nama ≤ 45
        if (($d['nama_proyek'] ?? '') !== '' && mb_strlen($d['nama_proyek']) > 45) {
            $errors['nama_proyek'] = 'Nama maksimal 45 karakter.';
        }

        // biaya: simpan sebagai digit saja
        if (($d['total_biaya_proyek'] ?? '') !== '') {
            $digits = preg_replace('/\D+/', '', $d['total_biaya_proyek']);
            if ($digits === '' || !ctype_digit($digits)) {
                $errors['total_biaya_proyek'] = 'Total biaya harus angka.';
            } else {
                $d['total_biaya_proyek'] = $digits;
            }
        }

        // tanggal: valid Y-m-d (meski di DB VARCHAR)
        foreach (['tanggal_mulai', 'tanggal_selesai'] as $t) {
            $v = $d[$t] ?? '';
            if ($v !== '') {
                $dt = DateTime::createFromFormat('Y-m-d', $v);
                $ok = $dt && $dt->format('Y-m-d') === $v;
                if (!$ok) $errors[$t] = 'Tanggal tidak valid.';
            }
        }

        // tanggal_selesai >= tanggal_mulai (jika keduanya ada)
        if (
            empty($errors['tanggal_mulai']) && empty($errors['tanggal_selesai'])
            && ($d['tanggal_mulai'] ?? '') !== '' && ($d['tanggal_selesai'] ?? '') !== ''
        ) {
            if ($d['tanggal_selesai'] < $d['tanggal_mulai']) {
                $errors['tanggal_selesai'] = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
            }
        }

        // status harus salah satu dari enum
        if (($d['status'] ?? '') !== '' && !in_array($d['status'], $this->statusEnum, true)) {
            $errors['status'] = 'Status tidak valid.';
        }

        return $errors;
    }

    private function handleUpload(string $field, bool $optional = false): array
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return $optional ? [] : ['path' => null];
        }

        $f = $_FILES[$field];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload gagal.'];
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'heic'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            return ['error' => 'Tipe file harus JPG, JPEG, PNG, atau HEIC.'];
        }

        // (opsional) MIME ringan
        $mime = mime_content_type($f['tmp_name']) ?: '';
        $okMime = str_contains($mime, 'jpeg') || str_contains($mime, 'png') || str_contains($mime, 'heic') || str_contains($mime, 'image/');
        if (!$okMime) {
            return ['error' => 'Tipe file tidak dikenali.'];
        }

        // === SIMPAN DI APP/UPLOADS/PROYEK ===
        // posisi file ini: app/controllers/ProyekController.php → naik 1 folder ke app/, lalu /uploads/proyek
        $dir = __DIR__ . '/../uploads/proyek';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);

        $basename = 'proyek_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        $destAbs  = $dir . '/' . $basename;

        if (!move_uploaded_file($f['tmp_name'], $destAbs)) {
            return ['error' => 'Gagal menyimpan file.'];
        }

        // Simpan path RELATIF terhadap /app (biar bisa diakses via $BASE_URL)
        // URL final: {BASE_URL}/uploads/proyek/xxxx.jpg  (contoh: http://localhost/.../app/uploads/proyek/xxxx.jpg)
        return ['path' => 'uploads/proyek/' . $basename];
    }
}
