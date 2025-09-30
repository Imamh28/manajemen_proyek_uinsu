<?php
require_once __DIR__ . '/../models/KaryawanModel.php';
require_once __DIR__ . '/../helpers/Audit.php';
require_once __DIR__ . '/../utils/csrf.php';
require_once __DIR__ . '/../helpers/Notify.php';
require_once __DIR__ . '/../utils/roles.php';

class KaryawanController
{
    private KaryawanModel $model;

    public function __construct(private PDO $pdo, private string $baseUrl)
    {
        $this->model   = new KaryawanModel($pdo);
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    /** GET /karyawan : listing + form tambah */
    public function index(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        $search = trim($_GET['search'] ?? '');
        $roles  = $this->model->roles();
        $rows   = $this->model->all($search);

        $BASE_URL = $this->baseUrl;

        // data unik untuk live validate (create)
        $existingIds    = array_values(array_unique(array_map('strval', array_column($rows, 'id_karyawan'))));
        $existingEmails = array_values(array_unique(array_map('strtolower', array_column($rows, 'email'))));
        $existingPhones = array_values(array_unique(array_map('strval', array_column($rows, 'no_telepon_karyawan'))));

        $EXISTING_IDS_JSON    = json_encode($existingIds,    JSON_UNESCAPED_UNICODE);
        $EXISTING_EMAILS_JSON = json_encode($existingEmails, JSON_UNESCAPED_UNICODE);
        $EXISTING_PHONES_JSON = json_encode($existingPhones, JSON_UNESCAPED_UNICODE);

        $karyawan = $rows;
        include __DIR__ . '/../views/admin/karyawan/main.php';
    }

    /** POST /karyawan/store */
    public function store(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=karyawan");
            exit;
        }

        $data = [
            'id_karyawan'         => strtoupper(trim($_POST['id_karyawan'] ?? '')),
            'nama_karyawan'       => trim($_POST['nama_karyawan'] ?? ''),
            'no_telepon_karyawan' => trim($_POST['no_telepon_karyawan'] ?? ''),
            'email'               => trim($_POST['email'] ?? ''),
            'password'            => trim($_POST['password'] ?? ''),
            'role_id_role'        => trim($_POST['role_id_role'] ?? ''),
        ];

        $errors = [];
        if ($data['id_karyawan'] === '')     $errors['id_karyawan']   = 'ID karyawan wajib diisi.';
        if ($data['nama_karyawan'] === '')   $errors['nama_karyawan'] = 'Nama wajib diisi.';
        if ($data['no_telepon_karyawan'] === '') $errors['no_telepon_karyawan'] = 'Nomor telepon wajib diisi.';
        if ($data['email'] === '')           $errors['email']         = 'Email wajib diisi.';
        if ($data['password'] === '')        $errors['password']      = 'Password wajib diisi.';
        if ($data['role_id_role'] === '')    $errors['role_id_role']  = 'Silakan pilih role.';

        if ($data['id_karyawan'] !== '' && !preg_match('/^KR\d+$/', $data['id_karyawan'])) {
            $errors['id_karyawan'] = 'ID harus diawali "KR" dan diikuti angka.';
        }
        if ($data['email'] !== '' && strpos($data['email'], '@') === false) {
            $errors['email'] = 'Email harus mengandung karakter "@".';
        }
        if ($data['nama_karyawan'] !== '' && mb_strlen($data['nama_karyawan']) > 20) {
            $errors['nama_karyawan'] = 'Nama maksimal 20 karakter.';
        }
        if ($data['password'] !== '' && strlen($data['password']) < 8) {
            $errors['password'] = 'Password minimal 8 karakter.';
        }
        if ($data['no_telepon_karyawan'] !== '' && !preg_match('/^\d{1,13}$/', $data['no_telepon_karyawan'])) {
            $errors['no_telepon_karyawan'] = 'Nomor telepon harus angka (maks. 13 digit).';
        }

        // unik
        if ($data['id_karyawan'] !== '' && $this->model->existsId($data['id_karyawan'])) {
            $errors['id_karyawan'] = 'ID karyawan sudah digunakan.';
        }
        if ($data['email'] !== '' && $this->model->existsEmail($data['email'])) {
            $errors['email'] = 'Email sudah digunakan.';
        }
        if ($data['no_telepon_karyawan'] !== '' && $this->model->existsPhone($data['no_telepon_karyawan'])) {
            $errors['no_telepon_karyawan'] = 'Nomor telepon sudah digunakan.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors']['karyawan_store'] = $errors;
            $old = $data;
            $old['password'] = '';
            $_SESSION['form_old']['karyawan_store'] = $old;
            header("Location: {$this->baseUrl}index.php?r=karyawan");
            exit;
        }

        try {
            if ($this->model->create($data)) {
                audit_log('karyawan_create', ['id' => $data['id_karyawan'], 'email' => $data['email']]);
                $_SESSION['success'] = 'Karyawan berhasil ditambahkan.';
                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'karyawan_created', [
                    'id' => $data['id_karyawan'],
                    'nama' => $data['nama_karyawan'],
                    'actor_id' => $actorId,
                ]);
            } else {
                $_SESSION['error'] = 'Gagal menambah karyawan.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menambah karyawan: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=karyawan");
        exit;
    }

    /** GET /karyawan/edit&id=KR001 */
    public function editForm(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        $id = trim($_GET['id'] ?? '');
        if ($id === '') {
            $_SESSION['error'] = 'ID karyawan tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=karyawan");
            exit;
        }

        $row   = $this->model->find($id);
        $roles = $this->model->roles();
        if (!$row) {
            $_SESSION['error'] = 'Data karyawan tidak ditemukan.';
            header("Location: {$this->baseUrl}index.php?r=karyawan");
            exit;
        }

        $BASE_URL = $this->baseUrl;

        // daftar email & telepon utk validasi live (edit)
        $all      = $this->model->all('');
        $exEmails = array_values(array_unique(array_map('strtolower', array_column($all, 'email'))));
        $exPhones = array_values(array_unique(array_map('strval', array_column($all, 'no_telepon_karyawan'))));
        $EXISTING_EMAILS_JSON = json_encode($exEmails, JSON_UNESCAPED_UNICODE);
        $EXISTING_PHONES_JSON = json_encode($exPhones, JSON_UNESCAPED_UNICODE);

        $karyawan = $row;
        include __DIR__ . '/../views/admin/karyawan/edit.php';
    }

    /** POST /karyawan/update */
    public function update(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=karyawan");
            exit;
        }

        $id   = strtoupper(trim($_POST['id_karyawan'] ?? ''));
        $data = [
            'nama_karyawan'       => trim($_POST['nama_karyawan'] ?? ''),
            'no_telepon_karyawan' => trim($_POST['no_telepon_karyawan'] ?? ''),
            'email'               => trim($_POST['email'] ?? ''),
            'password'            => trim($_POST['password'] ?? ''), // optional
            'role_id_role'        => trim($_POST['role_id_role'] ?? ''),
        ];

        $errors = [];
        if ($id === '')                           $errors['id_karyawan']   = 'ID tidak valid.';
        if ($data['nama_karyawan'] === '')        $errors['nama_karyawan'] = 'Nama wajib diisi.';
        if ($data['no_telepon_karyawan'] === '')  $errors['no_telepon_karyawan'] = 'Nomor telepon wajib diisi.';
        if ($data['email'] === '')                $errors['email']         = 'Email wajib diisi.';
        if ($data['role_id_role'] === '')         $errors['role_id_role']  = 'Silakan pilih role.';

        if ($data['email'] !== '' && strpos($data['email'], '@') === false) {
            $errors['email'] = 'Email harus mengandung "@"';
        }
        if ($data['nama_karyawan'] !== '' && mb_strlen($data['nama_karyawan']) > 20) {
            $errors['nama_karyawan'] = 'Nama maksimal 20 karakter.';
        }
        if ($data['password'] !== '' && strlen($data['password']) < 8) {
            $errors['password'] = 'Password baru minimal 8 karakter.';
        }
        if ($data['no_telepon_karyawan'] !== '' && !preg_match('/^\d{1,13}$/', $data['no_telepon_karyawan'])) {
            $errors['no_telepon_karyawan'] = 'Nomor telepon harus angka (maks. 13 digit).';
        }

        // unik (kecuali dirinya sendiri)
        if ($data['email'] !== '' && $this->model->existsEmailExcept($data['email'], $id)) {
            $errors['email'] = 'Email sudah digunakan oleh karyawan lain.';
        }
        if ($data['no_telepon_karyawan'] !== '' && $this->model->existsPhoneExcept($data['no_telepon_karyawan'], $id)) {
            $errors['no_telepon_karyawan'] = 'Nomor telepon sudah digunakan oleh karyawan lain.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors']['karyawan_update'] = $errors;
            $old = $data;
            $old['password'] = '';
            $_SESSION['form_old']['karyawan_update'] = $old;
            header("Location: {$this->baseUrl}index.php?r=karyawan/edit&id=" . urlencode($id));
            exit;
        }

        try {
            if ($this->model->update($id, $data)) {
                audit_log('karyawan_update', ['id' => $id, 'email' => $data['email']]);
                $_SESSION['success'] = 'Karyawan berhasil diperbarui.';
                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'karyawan_updated', [
                    'id' => $id,
                    'nama' => $data['nama_karyawan'],
                    'actor_id' => $actorId,
                ]);
            } else {
                $_SESSION['error'] = 'Tidak ada perubahan atau update gagal.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal memperbarui karyawan: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=karyawan");
        exit;
    }

    /** GET /karyawan/export */
    public function exportCSV(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        $search = trim($_GET['search'] ?? '');
        $rows   = $this->model->all($search);

        if (ob_get_level()) {
            while (ob_get_level()) {
                @ob_end_clean();
            }
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="karyawan-' . date('Ymd-His') . '.csv"');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Nama', 'Email', 'Telepon', 'Role']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id_karyawan'],
                $r['nama_karyawan'],
                $r['email'],
                $r['no_telepon_karyawan'],
                $r['nama_role'],
            ]);
        }
        fclose($out);

        audit_log('karyawan_export', ['count' => count($rows), 'search' => $search]);
        exit;
    }

    /** POST /karyawan/delete */
    public function delete(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=karyawan");
            exit;
        }

        $id = trim($_POST['hapus_id'] ?? '');
        if ($id === '') {
            $_SESSION['error'] = 'ID karyawan tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=karyawan");
            exit;
        }

        try {
            if (!$this->model->canDelete($id)) {
                $_SESSION['error'] = 'Karyawan sedang dipakai di proyek (PIC). Tidak dapat dihapus.';
            } else {
                if ($this->model->delete($id)) {
                    audit_log('karyawan_delete', ['id' => $id]);
                    $_SESSION['success'] = 'Karyawan berhasil dihapus.';
                    $actorId = (string)($_SESSION['user']['id'] ?? '');
                    notif_event($this->pdo, 'karyawan_deleted', [
                        'id' => $id,
                        'actor_id' => $actorId,
                    ]);
                } else {
                    $_SESSION['error'] = 'Gagal menghapus karyawan.';
                }
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menghapus karyawan: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=karyawan");
        exit;
    }
}
