<?php
// app/controllers/KlienController.php
require_once __DIR__ . '/../models/KlienModel.php';
require_once __DIR__ . '/../helpers/Audit.php';
require_once __DIR__ . '/../utils/csrf.php';
require_once __DIR__ . '/../helpers/Notify.php'; // <-- penting: untuk notif_event

class KlienController
{
    private KlienModel $model;
    public function __construct(private PDO $pdo, private string $baseUrl)
    {
        $this->model   = new KlienModel($pdo);
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    /** GET /klien : list + form tambah */
    public function index(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        $search = trim($_GET['search'] ?? '');
        $rows   = $this->model->all($search);

        // untuk view
        $BASE_URL = $this->baseUrl;
        $kliens   = $rows;

        // ===== data unik utk live-validate =====
        $existingIds    = array_values(array_unique(array_map('strval',  array_column($rows, 'id_klien'))));
        $existingEmails = array_values(array_unique(array_map('strtolower', array_column($rows, 'email_klien'))));
        $existingNames  = array_values(array_unique(array_map('strtolower', array_column($rows, 'nama_klien'))));
        $existingPhones = array_values(array_unique(array_map('strval',  array_column($rows, 'no_telepon_klien'))));

        $EXISTING_IDS_JSON    = json_encode($existingIds,    JSON_UNESCAPED_UNICODE);
        $EXISTING_EMAILS_JSON = json_encode($existingEmails, JSON_UNESCAPED_UNICODE);
        $EXISTING_NAMES_JSON  = json_encode($existingNames,  JSON_UNESCAPED_UNICODE);
        $EXISTING_PHONES_JSON = json_encode($existingPhones, JSON_UNESCAPED_UNICODE);
        // ======================================

        include __DIR__ . '/../views/admin/klien/main.php';
    }

    /** POST /klien/store */
    public function store(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid. Silakan ulangi.';
            header("Location: {$this->baseUrl}index.php?r=klien");
            exit;
        }

        // normalisasi
        $data = [
            'id_klien'         => strtoupper(trim($_POST['id_klien'] ?? '')),
            'nama_klien'       => trim($_POST['nama_klien'] ?? ''),
            'no_telepon_klien' => trim($_POST['no_telepon_klien'] ?? ''),
            'email_klien'      => trim($_POST['email_klien'] ?? ''),
            'alamat_klien'     => trim($_POST['alamat_klien'] ?? ''),
        ];

        $errors = [];

        // required
        foreach (['id_klien', 'nama_klien', 'no_telepon_klien', 'email_klien', 'alamat_klien'] as $f) {
            if ($data[$f] === '') $errors[$f] = 'Wajib diisi.';
        }

        // aturan khusus
        if ($data['id_klien'] !== '' && !str_starts_with($data['id_klien'], 'KL')) {
            $errors['id_klien'] = 'ID klien harus diawali "KL".';
        }
        if ($data['email_klien'] !== '' && strpos($data['email_klien'], '@') === false) {
            $errors['email_klien'] = 'Email harus mengandung karakter "@".';
        }
        if ($data['nama_klien'] !== '' && mb_strlen($data['nama_klien']) > 40) {
            $errors['nama_klien'] = 'Nama maksimal 40 karakter.';
        }
        if ($data['no_telepon_klien'] !== '') {
            if (!preg_match('/^\d+$/', $data['no_telepon_klien'])) {
                $errors['no_telepon_klien'] = 'Nomor telepon hanya boleh angka.';
            } elseif (strlen($data['no_telepon_klien']) > 13) {
                $errors['no_telepon_klien'] = 'Nomor telepon maksimal 13 digit.';
            }
        }

        // unik
        if ($data['id_klien'] !== '' && $this->model->existsId($data['id_klien'])) {
            $errors['id_klien'] = 'ID klien sudah digunakan.';
        }
        if ($data['email_klien'] !== '' && $this->model->existsEmail($data['email_klien'])) {
            $errors['email_klien'] = 'Email sudah digunakan.';
        }
        if ($data['nama_klien'] !== '' && $this->model->existsNama($data['nama_klien'])) {
            $errors['nama_klien'] = 'Nama klien sudah digunakan.';
        }
        if ($data['no_telepon_klien'] !== '' && $this->model->existsPhone($data['no_telepon_klien'])) {
            $errors['no_telepon_klien'] = 'Nomor telepon sudah digunakan.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors']['klien_store'] = $errors;
            $_SESSION['form_old']['klien_store']    = $data;
            header("Location: {$this->baseUrl}index.php?r=klien");
            exit;
        }

        try {
            if ($this->model->create($data)) {
                audit_log('klien_create', ['id' => $data['id_klien'], 'email' => $data['email_klien']]);
                $_SESSION['success'] = 'Klien berhasil ditambahkan.';

                // === NOTIFIKASI: broadcast ke semua, SKIP admin pelaku ===
                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'klien_created', [
                    'id'       => $data['id_klien'],
                    'nama'     => $data['nama_klien'],
                    'actor_id' => $actorId
                ]);
            } else {
                $_SESSION['error'] = 'Gagal menambah klien.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menambah klien: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=klien");
        exit;
    }

    /** GET /klien/edit&id=... */
    public function editForm(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        $id = trim($_GET['id'] ?? '');
        if ($id === '') {
            $_SESSION['error'] = 'ID tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=klien");
            exit;
        }

        $row = $this->model->find($id);
        if (!$row) {
            $_SESSION['error'] = 'Data tidak ditemukan.';
            header("Location: {$this->baseUrl}index.php?r=klien");
            exit;
        }

        $BASE_URL = $this->baseUrl;
        $klien    = $row;

        // daftar unik utk live-validate saat edit
        $all       = $this->model->all('');
        $exEmails  = array_values(array_unique(array_map('strtolower', array_column($all, 'email_klien'))));
        $exNames   = array_values(array_unique(array_map('strtolower', array_column($all, 'nama_klien'))));
        $exPhones  = array_values(array_unique(array_map('strval',     array_column($all, 'no_telepon_klien'))));

        $EXISTING_EMAILS_JSON = json_encode($exEmails, JSON_UNESCAPED_UNICODE);
        $EXISTING_NAMES_JSON  = json_encode($exNames,  JSON_UNESCAPED_UNICODE);
        $EXISTING_PHONES_JSON = json_encode($exPhones, JSON_UNESCAPED_UNICODE);

        include __DIR__ . '/../views/admin/klien/edit.php';
    }

    /** POST /klien/update */
    public function update(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid. Silakan ulangi.';
            header("Location: {$this->baseUrl}index.php?r=klien");
            exit;
        }

        $id  = trim($_POST['id_klien'] ?? '');
        $data = [
            'nama_klien'       => trim($_POST['nama_klien'] ?? ''),
            'no_telepon_klien' => trim($_POST['no_telepon_klien'] ?? ''),
            'email_klien'      => trim($_POST['email_klien'] ?? ''),
            'alamat_klien'     => trim($_POST['alamat_klien'] ?? ''),
        ];

        $errors = [];
        if ($id === '') $errors['id_klien'] = 'ID tidak valid.';

        foreach (['nama_klien', 'no_telepon_klien', 'email_klien', 'alamat_klien'] as $f) {
            if ($data[$f] === '') $errors[$f] = 'Wajib diisi.';
        }

        if ($data['email_klien'] !== '' && strpos($data['email_klien'], '@') === false) {
            $errors['email_klien'] = 'Email harus mengandung karakter "@".';
        }
        if ($data['nama_klien'] !== '' && mb_strlen($data['nama_klien']) > 40) {
            $errors['nama_klien'] = 'Nama maksimal 40 karakter.';
        }
        if ($data['no_telepon_klien'] !== '') {
            if (!preg_match('/^\d+$/', $data['no_telepon_klien'])) {
                $errors['no_telepon_klien'] = 'Nomor telepon hanya boleh angka.';
            } elseif (strlen($data['no_telepon_klien']) > 13) {
                $errors['no_telepon_klien'] = 'Nomor telepon maksimal 13 digit.';
            }
        }

        // unik (kecuali dirinya sendiri)
        if ($data['email_klien'] !== '' && $this->model->existsEmailExcept($data['email_klien'], $id)) {
            $errors['email_klien'] = 'Email sudah digunakan klien lain.';
        }
        if ($data['nama_klien'] !== '' && $this->model->existsNamaExcept($data['nama_klien'], $id)) {
            $errors['nama_klien'] = 'Nama klien sudah dipakai.';
        }
        if ($data['no_telepon_klien'] !== '' && $this->model->existsPhoneExcept($data['no_telepon_klien'], $id)) {
            $errors['no_telepon_klien'] = 'Nomor telepon sudah digunakan.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors']['klien_update'] = $errors;
            $_SESSION['form_old']['klien_update']    = $data;
            header("Location: {$this->baseUrl}index.php?r=klien/edit&id=" . urlencode($id));
            exit;
        }

        try {
            if ($this->model->update($id, $data)) {
                audit_log('klien_update', ['id' => $id, 'email' => $data['email_klien']]);
                $_SESSION['success'] = 'Klien berhasil diperbarui.';

                // === NOTIFIKASI: broadcast ke semua, SKIP admin pelaku ===
                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'klien_updated', [
                    'id'       => $id,
                    'nama'     => $data['nama_klien'],
                    'actor_id' => $actorId
                ]);
            } else {
                $_SESSION['error'] = 'Tidak ada perubahan atau update gagal.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal memperbarui klien: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=klien");
        exit;
    }

    /** POST /klien/delete (via modal) */
    public function delete(): void
    {
        require_roles(['RL001'], $this->baseUrl);
        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir/CSRF tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=klien");
            exit;
        }

        $id = trim($_POST['hapus_id'] ?? '');
        if ($id === '') {
            $_SESSION['error'] = 'ID tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=klien");
            exit;
        }

        try {
            if (!$this->model->canDelete($id)) {
                $_SESSION['error'] = 'Klien terpakai di proyek. Tidak dapat dihapus.';
            } else {
                $ok = $this->model->delete($id);
                if ($ok) {
                    audit_log('klien.delete', ['id' => $id]);
                    $_SESSION['success'] = 'Klien berhasil dihapus.';

                    // === NOTIFIKASI: broadcast ke semua, SKIP admin pelaku ===
                    $actorId = (string)($_SESSION['user']['id'] ?? '');
                    notif_event($this->pdo, 'klien_deleted', [
                        'id'       => $id,
                        'actor_id' => $actorId
                    ]);
                } else {
                    $_SESSION['error'] = 'Gagal menghapus klien.';
                }
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menghapus klien: ' . $e->getMessage();
        }
        header("Location: {$this->baseUrl}index.php?r=klien");
        exit;
    }

    /** GET /klien/export (tanpa layout) */
    public function exportCSV(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        $q = trim($_GET['search'] ?? '');
        $rows = $this->model->exportRows($q);

        $filename = 'klien-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // BOM utk Excel
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        // header
        fputcsv($out, ['ID', 'Nama', 'Telepon', 'Email', 'Alamat']);
        foreach ($rows as $r) fputcsv($out, array_values($r));
        fclose($out);
        audit_log('klien.export', ['search' => $q, 'count' => count($rows)]);
        exit;
    }
}
