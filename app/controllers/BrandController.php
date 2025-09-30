<?php
require_once __DIR__ . '/../models/BrandModel.php';
require_once __DIR__ . '/../helpers/Audit.php';
require_once __DIR__ . '/../utils/csrf.php';
require_once __DIR__ . '/../helpers/Notify.php';

class BrandController
{
    private BrandModel $model;

    public function __construct(private PDO $pdo, private string $baseUrl)
    {
        $this->model   = new BrandModel($pdo);
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    private function actor(): array
    {
        return [
            'id'   => (string)($_SESSION['user']['id'] ?? '-'),
            'name' => $_SESSION['user']['name'] ?? '-',
            'role' => $_SESSION['user']['role_name'] ?? '-',
        ];
    }

    /** GET /brand : list + form tambah */
    public function index(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        $search   = trim($_GET['search'] ?? '');
        $rows     = $this->model->all($search);
        $BASE_URL = $this->baseUrl;

        // Ambil semua nama brand dan normalisasi ke lowercase untuk live check (case-insensitive)
        $names = $this->model->names();
        $namesLower = array_values(array_unique(array_map(
            fn($s) => mb_strtolower(trim((string)$s)),
            $names
        )));
        $EXISTING_BRAND_NAMES_JSON = json_encode($namesLower, JSON_UNESCAPED_UNICODE);

        $brands = $rows;
        include __DIR__ . '/../views/admin/brand/main.php';
    }

    /** POST /brand/store */
    public function store(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=brand");
            exit;
        }

        $data = [
            'nama_brand' => trim($_POST['nama_brand'] ?? '')
        ];
        $errors = [];

        if ($data['nama_brand'] === '')                 $errors['nama_brand'] = 'Nama brand wajib diisi.';
        if (mb_strlen($data['nama_brand']) > 45)        $errors['nama_brand'] = 'Maksimal 45 karakter.';
        if ($data['nama_brand'] !== '' && $this->model->existsName($data['nama_brand']))
            $errors['nama_brand'] = 'Nama brand sudah ada.';

        if ($errors) {
            $_SESSION['form_errors']['brand_store'] = $errors;
            $_SESSION['form_old']['brand_store']    = $data;
            header("Location: {$this->baseUrl}index.php?r=brand");
            exit;
        }

        try {
            if ($this->model->create($data)) {
                $_SESSION['success'] = 'Brand berhasil ditambahkan.';
                audit_log('BRAND_CREATE', ['nama' => $data['nama_brand']]);

                // === NOTIFIKASI: broadcast ke semua role, tapi SKIP admin pelaku ===
                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'brand_created', [
                    'nama'      => $data['nama_brand'],
                    'actor_id'  => $actorId
                ]);
            } else {
                $_SESSION['error'] = 'Gagal menambah brand.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menambah brand: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=brand");
        exit;
    }

    /** GET /brand/edit&id=1 */
    public function editForm(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'ID tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=brand");
            exit;
        }

        $row = $this->model->find($id);
        if (!$row) {
            $_SESSION['error'] = 'Data tidak ditemukan.';
            header("Location: {$this->baseUrl}index.php?r=brand");
            exit;
        }

        $BASE_URL = $this->baseUrl;
        $brand    = $row;

        // ===== inject daftar nama utk live unique check (case-insensitive) =====
        $all = $this->model->all('');
        $exNames = array_values(
            array_unique(array_map('strtolower', array_column($all, 'nama_brand')))
        );
        $EXISTING_BRAND_NAMES_JSON = json_encode($exNames, JSON_UNESCAPED_UNICODE);
        // =======================================================================

        include __DIR__ . '/../views/admin/brand/edit.php';
    }

    /** POST /brand/update */
    public function update(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=brand");
            exit;
        }

        $id   = (int)($_POST['id_brand'] ?? 0);
        $data = ['nama_brand' => trim($_POST['nama_brand'] ?? '')];

        $errors = [];
        if ($id <= 0)                                  $errors['id_brand']   = 'ID tidak valid.';
        if ($data['nama_brand'] === '')                $errors['nama_brand'] = 'Nama brand wajib diisi.';
        if (mb_strlen($data['nama_brand']) > 45)       $errors['nama_brand'] = 'Maksimal 45 karakter.';
        if ($data['nama_brand'] !== '' && $this->model->existsNameExcept($data['nama_brand'], $id))
            $errors['nama_brand'] = 'Nama brand sudah ada.';

        if ($errors) {
            $_SESSION['form_errors']['brand_update'] = $errors;
            $_SESSION['form_old']['brand_update']    = $data;
            header("Location: {$this->baseUrl}index.php?r=brand/edit&id={$id}");
            exit;
        }

        try {
            if ($this->model->update($id, $data)) {
                $_SESSION['success'] = 'Brand berhasil diperbarui.';
                audit_log('BRAND_UPDATE', ['id' => $id, 'nama' => $data['nama_brand']]);

                // === NOTIFIKASI: broadcast ke semua role, SKIP admin pelaku ===
                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'brand_updated', [
                    'id'        => $id,
                    'nama'      => $data['nama_brand'],
                    'actor_id'  => $actorId
                ]);
            } else {
                $_SESSION['error'] = 'Tidak ada perubahan atau gagal update.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal memperbarui brand: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=brand");
        exit;
    }

    /** POST /brand/delete */
    public function delete(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=brand");
            exit;
        }

        $id = (int)($_POST['hapus_id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'ID tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=brand");
            exit;
        }

        try {
            if (!$this->model->canDelete($id)) {
                $_SESSION['error'] = 'Brand sedang dipakai di proyek. Tidak bisa dihapus.';
            } else {
                if ($this->model->delete($id)) {
                    $_SESSION['success'] = 'Brand berhasil dihapus.';
                    audit_log('BRAND_DELETE', ['id' => $id]);

                    // === NOTIFIKASI: broadcast ke semua role, SKIP admin pelaku ===
                    $actorId = (string)($_SESSION['user']['id'] ?? '');
                    notif_event($this->pdo, 'brand_deleted', [
                        'id'        => $id,
                        'actor_id'  => $actorId
                    ]);
                } else {
                    $_SESSION['error'] = 'Gagal menghapus brand.';
                }
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menghapus brand: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=brand");
        exit;
    }

    /** GET /brand/export&search=... (tanpa layout) */
    public function exportCSV(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        $search = trim($_GET['search'] ?? '');
        $rows   = $this->model->all($search);

        $fname  = 'brand_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fname . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // Optional: BOM utf-8 supaya Excel nyaman
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, ['ID', 'Nama Brand']);
        foreach ($rows as $r) {
            fputcsv($out, [$r['id_brand'], $r['nama_brand']]);
        }
        fclose($out);

        audit_log('BRAND_EXPORT', [
            'count'  => count($rows),
            'search' => $search
        ]);
        exit;
    }
}
