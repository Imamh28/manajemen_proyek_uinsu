<?php
require_once __DIR__ . '/../models/TahapanModel.php';
require_once __DIR__ . '/../helpers/Audit.php';
require_once __DIR__ . '/../utils/csrf.php';
require_once __DIR__ . '/../helpers/Notify.php';

class TahapanController
{
    private TahapanModel $model;

    public function __construct(private PDO $pdo, private string $baseUrl)
    {
        $this->model   = new TahapanModel($pdo);
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    /** GET /tahapan */
    public function index(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        $search   = trim($_GET['search'] ?? '');
        $rows     = $this->model->all($search);
        $BASE_URL = $this->baseUrl;

        // === untuk validasi live ===
        $ids = array_values(array_unique(array_map(
            fn($s) => strtoupper((string)$s),
            $this->model->allIds()
        )));
        $namesLower = array_values(array_unique(array_map(
            fn($s) => mb_strtolower(trim((string)$s)),
            $this->model->allNames()
        )));
        $EXISTING_IDS_JSON   = json_encode($ids, JSON_UNESCAPED_UNICODE);
        $EXISTING_NAMES_JSON = json_encode($namesLower, JSON_UNESCAPED_UNICODE);
        // ===========================

        $tahapan = $rows;
        include __DIR__ . '/../views/admin/tahapan/main.php';
    }

    /** POST /tahapan/store */
    public function store(): void
    {
        require_roles(['RL001'], $this->baseUrl);
        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=tahapan");
            exit;
        }

        // ID wajib format TH + digit
        $idInput = strtoupper(trim($_POST['id_tahapan'] ?? ''));
        $data = [
            'id_tahapan'   => $idInput,
            'nama_tahapan' => trim($_POST['nama_tahapan'] ?? ''),
        ];

        $errors = [];
        if ($data['id_tahapan'] === '')                     $errors['id_tahapan']   = 'ID wajib diisi.';
        if ($data['nama_tahapan'] === '')                   $errors['nama_tahapan'] = 'Nama wajib diisi.';
        if (mb_strlen($data['nama_tahapan']) > 45)          $errors['nama_tahapan'] = 'Nama maksimal 45 karakter.';

        // format ID yang benar: TH diikuti angka saja
        if ($data['id_tahapan'] !== '' && !preg_match('/^TH\d+$/', $data['id_tahapan'])) {
            $errors['id_tahapan'] = 'ID harus diawali "TH" dan setelahnya hanya angka.';
        }

        // unik
        if (empty($errors['id_tahapan']) && $this->model->existsId($data['id_tahapan'])) {
            $errors['id_tahapan'] = 'ID tahapan sudah digunakan.';
        }
        if (empty($errors['nama_tahapan']) && $this->model->existsName($data['nama_tahapan'])) {
            $errors['nama_tahapan'] = 'Nama tahapan sudah digunakan.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors']['tahapan_store'] = $errors;
            $_SESSION['form_old']['tahapan_store']    = $data;
            header("Location: {$this->baseUrl}index.php?r=tahapan");
            exit;
        }

        try {
            if ($this->model->create($data)) {
                audit_log('tahapan.create', $data);
                $_SESSION['success'] = 'Tahapan berhasil ditambahkan.';
                // === NOTIFIKASI: Tahapan dibuat (skip pelaku) ===
                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'tahapan_created', [
                    'id'       => $data['id_tahapan'],
                    'nama'     => $data['nama_tahapan'],
                    'actor_id' => $actorId,
                ]);
            } else {
                $_SESSION['error'] = 'Gagal menambah tahapan.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menambah tahapan: ' . $e->getMessage();
        }
        header("Location: {$this->baseUrl}index.php?r=tahapan");
        exit;
    }

    /** GET /tahapan/edit&id=TH01 */
    public function editForm(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        $id = trim($_GET['id'] ?? '');
        if ($id === '') {
            $_SESSION['error'] = 'ID tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=tahapan");
            exit;
        }

        $row = $this->model->find($id);
        if (!$row) {
            $_SESSION['error'] = 'Data tidak ditemukan.';
            header("Location: {$this->baseUrl}index.php?r=tahapan");
            exit;
        }

        $BASE_URL = $this->baseUrl;
        $tahapan  = $row;

        // untuk live unique check nama (case-insensitive)
        $namesLower = array_values(array_unique(array_map(
            fn($s) => mb_strtolower(trim((string)$s)),
            $this->model->allNames()
        )));
        $EXISTING_NAMES_JSON = json_encode($namesLower, JSON_UNESCAPED_UNICODE);

        include __DIR__ . '/../views/admin/tahapan/edit.php';
    }

    /** POST /tahapan/update */
    public function update(): void
    {
        require_roles(['RL001'], $this->baseUrl);
        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=tahapan");
            exit;
        }

        $id   = trim($_POST['id_tahapan'] ?? '');
        $data = ['nama_tahapan' => trim($_POST['nama_tahapan'] ?? '')];

        $errors = [];
        if ($id === '')                                 $errors['id_tahapan']   = 'ID tidak valid.';
        if ($data['nama_tahapan'] === '')               $errors['nama_tahapan'] = 'Nama wajib diisi.';
        if (mb_strlen($data['nama_tahapan']) > 45)      $errors['nama_tahapan'] = 'Nama maksimal 45 karakter.';
        if ($data['nama_tahapan'] !== '' && $this->model->existsNameExcept($data['nama_tahapan'], $id)) {
            $errors['nama_tahapan'] = 'Nama tahapan sudah digunakan.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors']['tahapan_update'] = $errors;
            $_SESSION['form_old']['tahapan_update']    = $data;
            header("Location: {$this->baseUrl}index.php?r=tahapan/edit&id=" . urlencode($id));
            exit;
        }

        try {
            if ($this->model->update($id, $data)) {
                audit_log('tahapan.update', ['id' => $id, 'new' => $data]);
                $_SESSION['success'] = 'Tahapan berhasil diperbarui.';
                // === NOTIFIKASI: Tahapan diubah (skip pelaku) ===
                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'tahapan_updated', [
                    'id'       => $id,
                    'nama'     => $data['nama_tahapan'],
                    'actor_id' => $actorId,
                ]);
            } else {
                $_SESSION['error'] = 'Tidak ada perubahan atau update gagal.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal memperbarui tahapan: ' . $e->getMessage();
        }
        header("Location: {$this->baseUrl}index.php?r=tahapan");
        exit;
    }

    /** POST /tahapan/delete */
    public function delete(): void
    {
        require_roles(['RL001'], $this->baseUrl);
        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=tahapan");
            exit;
        }

        $id = trim($_POST['hapus_id'] ?? '');
        if ($id === '') {
            $_SESSION['error'] = 'ID tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=tahapan");
            exit;
        }

        try {
            if (!$this->model->canDelete($id)) {
                $_SESSION['error'] = 'Tahapan sedang dipakai pada jadwal proyek. Tidak bisa dihapus.';
            } else {
                if ($this->model->delete($id)) {
                    audit_log('tahapan.delete', ['id' => $id]);
                    $_SESSION['success'] = 'Tahapan berhasil dihapus.';
                    // === NOTIFIKASI: Tahapan dihapus (skip pelaku) ===
                    $actorId = (string)($_SESSION['user']['id'] ?? '');
                    notif_event($this->pdo, 'tahapan_deleted', [
                        'id'       => $id,
                        'actor_id' => $actorId,
                    ]);
                } else {
                    $_SESSION['error'] = 'Gagal menghapus tahapan.';
                }
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menghapus tahapan: ' . $e->getMessage();
        }
        header("Location: {$this->baseUrl}index.php?r=tahapan");
        exit;
    }

    /** GET /tahapan/export */
    public function exportCSV(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        $search = trim($_GET['search'] ?? '');
        $rows   = $this->model->all($search);

        $filename = 'tahapan-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache');

        $out = fopen('php://output', 'w');
        // BOM UTF-8
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['ID Tahapan', 'Nama Tahapan']);
        foreach ($rows as $r) fputcsv($out, [$r['id_tahapan'], $r['nama_tahapan']]);
        fclose($out);

        audit_log('tahapan.export', ['search' => $search, 'rows' => count($rows)]);
        exit;
    }
}
