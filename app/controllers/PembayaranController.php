<?php
// app/controllers/PembayaranController.php
require_once __DIR__ . '/../utils/roles.php';
require_once __DIR__ . '/../helpers/Audit.php';
require_once __DIR__ . '/../utils/csrf.php';
require_once __DIR__ . '/../helpers/Notify.php';

class PembayaranController
{
    public function __construct(private PDO $pdo, private string $baseUrl)
    {
        require_once __DIR__ . '/../models/PembayaranModel.php';
        $this->model = new PembayaranModel($this->pdo);
    }

    /** GET /pembayaran : list + form tambah */
    public function index(): void
    {
        // akses dijaga oleh menu (RL001 & RL002) lewat router/authorize
        $q      = trim($_GET['search'] ?? '');
        $rows   = $this->model->all($q);

        $proyekList = $this->model->projects();

        // kirim ke view
        $BASE_URL = $this->baseUrl;
        $pembayarans = $rows;
        $jenisEnum   = ['DP', 'Termin', 'Pelunasan'];
        $statusEnum  = ['Belum Lunas', 'Lunas'];

        // untuk live-validate unik ID + prefix PAY
        $EXISTING_IDS_JSON = json_encode(
            array_values(array_unique(array_map('strval', $this->model->existingIds()))),
            JSON_UNESCAPED_UNICODE
        );

        // pilih folder view berdasar role
        $roleDir = role_dir($_SESSION['user']['role_id'] ?? '');
        include __DIR__ . "/../views/{$roleDir}/pembayaran/main.php";
    }

    /** POST /pembayaran/store */
    public function store(): void
    {
        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

        // helper
        $digits = fn(string $s) => preg_replace('~\D+~', '', $s);

        // terima input
        $d = [
            'id_pem_bayaran'   => trim($_POST['id_pem_bayaran'] ?? ''),
            'jenis_pembayaran' => trim($_POST['jenis_pembayaran'] ?? ''),
            'sub_total'        => $digits($_POST['sub_total'] ?? ''),
            'pajak_pembayaran' => $digits($_POST['pajak_pembayaran'] ?? ''), // akan direcalc
            'total_pembayaran' => $digits($_POST['total_pembayaran'] ?? ''), // akan direcalc
            'tanggal_jatuh_tempo' => trim($_POST['tanggal_jatuh_tempo'] ?? ''),
            'tanggal_bayar'       => trim($_POST['tanggal_bayar'] ?? ''),
            'status_pembayaran'   => trim($_POST['status_pembayaran'] ?? ''),
            'proyek_id_proyek'    => trim($_POST['proyek_id_proyek'] ?? ''),
            'bukti_pembayaran'    => null, // isi setelah upload
        ];

        // hitung ulang (server-trust)
        $sub   = (int)($d['sub_total'] ?: 0);
        $pajak = (int) round($sub * 0.10);
        $total = $sub + $pajak;
        $d['pajak_pembayaran'] = (string)$pajak;
        $d['total_pembayaran'] = (string)$total;

        // validasi
        $err = [];

        // required
        foreach (
            [
                'id_pem_bayaran',
                'jenis_pembayaran',
                'sub_total',
                'tanggal_jatuh_tempo',
                'tanggal_bayar',
                'status_pembayaran',
                'proyek_id_proyek'
            ] as $f
        ) {
            if (($d[$f] ?? '') === '') $err[$f] = 'Wajib diisi.';
        }

        // prefix PAY + unik
        if ($d['id_pem_bayaran'] !== '') {
            if (!str_starts_with($d['id_pem_bayaran'], 'PAY')) {
                $err['id_pem_bayaran'] = 'ID harus diawali "PAY".';
            }
            if ($this->model->existsId($d['id_pem_bayaran'])) {
                $err['id_pem_bayaran'] = 'ID pembayaran sudah digunakan.';
            }
        }

        // numeric
        if ($sub <= 0) $err['sub_total'] = 'Sub total harus angka > 0.';

        // tanggal (YYYY-MM-DD)
        $isDate = fn($s) => $s && DateTime::createFromFormat('Y-m-d', $s) !== false;
        if ($d['tanggal_jatuh_tempo'] && !$isDate($d['tanggal_jatuh_tempo'])) $err['tanggal_jatuh_tempo'] = 'Tanggal tidak valid.';
        if ($d['tanggal_bayar'] && !$isDate($d['tanggal_bayar'])) $err['tanggal_bayar'] = 'Tanggal tidak valid.';

        // file bukti: wajib saat create
        if (empty($_FILES['bukti_pembayaran']['name'])) {
            $err['bukti_pembayaran'] = 'Bukti pembayaran wajib diunggah.';
        } else {
            $ok = $this->validateUpload($_FILES['bukti_pembayaran'], $msg);
            if (!$ok) $err['bukti_pembayaran'] = $msg ?: 'File tidak valid.';
        }

        if ($err) {
            $_SESSION['form_errors']['pembayaran_store'] = $err;
            $_SESSION['form_old']['pembayaran_store']    = $_POST;
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

        // simpan file
        $buktiPath = null;
        if (!empty($_FILES['bukti_pembayaran']['name'])) {
            $buktiPath = $this->moveUpload($_FILES['bukti_pembayaran'], $d['id_pem_bayaran']);
        }
        $d['bukti_pembayaran'] = $buktiPath;

        try {
            $ok = $this->model->create($d);
            if ($ok) {
                audit_log('pembayaran.create', $d);
                $_SESSION['success'] = 'Pembayaran berhasil ditambahkan.';
                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'pembayaran_created', [
                    'id'        => $d['id_pem_bayaran'],
                    'proyek_id' => $d['proyek_id_proyek'],
                    'jenis'     => $d['jenis_pembayaran'],
                    'actor_id'  => $actorId,
                ]);
            } else {
                $_SESSION['error'] = 'Gagal menambah pembayaran.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menambah pembayaran: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=pembayaran");
        exit;
    }

    /** GET /pembayaran/edit&id=PAY001 */
    public function editForm(): void
    {
        $id = trim($_GET['id'] ?? '');
        if ($id === '') {
            $_SESSION['error'] = 'ID tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

        $row = $this->model->find($id);
        if (!$row) {
            $_SESSION['error'] = 'Data tidak ditemukan.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

        $proyekList = $this->model->projects();
        $BASE_URL   = $this->baseUrl;
        $pembayaran = $row;
        $jenisEnum  = ['DP', 'Termin', 'Pelunasan'];
        $statusEnum = ['Belum Lunas', 'Lunas'];

        $roleDir = role_dir($_SESSION['user']['role_id'] ?? '');

        // old/error
        $__updErr = $_SESSION['form_errors']['pembayaran_update'] ?? [];
        $__updOld = $_SESSION['form_old']['pembayaran_update'] ?? [];
        unset($_SESSION['form_errors']['pembayaran_update'], $_SESSION['form_old']['pembayaran_update']);

        include __DIR__ . "/../views/{$roleDir}/pembayaran/edit.php";
    }

    /** POST /pembayaran/update */
    public function update(): void
    {
        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

        $id = trim($_POST['id_pem_bayaran'] ?? '');
        if ($id === '') {
            $_SESSION['error'] = 'ID tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

        $row = $this->model->find($id);
        if (!$row) {
            $_SESSION['error'] = 'Data tidak ditemukan.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

        $digits = fn(string $s) => preg_replace('~\D+~', '', $s);

        $d = [
            'jenis_pembayaran' => trim($_POST['jenis_pembayaran'] ?? ''),
            'sub_total'        => $digits($_POST['sub_total'] ?? ''),
            'pajak_pembayaran' => $digits($_POST['pajak_pembayaran'] ?? ''),
            'total_pembayaran' => $digits($_POST['total_pembayaran'] ?? ''),
            'tanggal_jatuh_tempo' => trim($_POST['tanggal_jatuh_tempo'] ?? ''),
            'tanggal_bayar'       => trim($_POST['tanggal_bayar'] ?? ''),
            'status_pembayaran'   => trim($_POST['status_pembayaran'] ?? ''),
            'proyek_id_proyek'    => trim($_POST['proyek_id_proyek'] ?? ''),
            'bukti_pembayaran'    => $row['bukti_pembayaran'],
        ];

        // recalc
        $sub   = (int)($d['sub_total'] ?: 0);
        $pajak = (int) round($sub * 0.10);
        $total = $sub + $pajak;
        $d['pajak_pembayaran'] = (string)$pajak;
        $d['total_pembayaran'] = (string)$total;

        $err = [];
        foreach (
            [
                'jenis_pembayaran',
                'sub_total',
                'tanggal_jatuh_tempo',
                'tanggal_bayar',
                'status_pembayaran',
                'proyek_id_proyek'
            ] as $f
        ) {
            if (($d[$f] ?? '') === '') $err[$f] = 'Wajib diisi.';
        }

        $isDate = fn($s) => $s && DateTime::createFromFormat('Y-m-d', $s) !== false;
        if ($d['tanggal_jatuh_tempo'] && !$isDate($d['tanggal_jatuh_tempo'])) $err['tanggal_jatuh_tempo'] = 'Tanggal tidak valid.';
        if ($d['tanggal_bayar'] && !$isDate($d['tanggal_bayar'])) $err['tanggal_bayar'] = 'Tanggal tidak valid.';
        if ($sub <= 0) $err['sub_total'] = 'Sub total harus angka > 0.';

        // file (opsional ganti)
        if (!empty($_FILES['bukti_pembayaran']['name'])) {
            $ok = $this->validateUpload($_FILES['bukti_pembayaran'], $msg);
            if (!$ok) {
                $err['bukti_pembayaran'] = $msg ?: 'File tidak valid.';
            }
        }

        if ($err) {
            $_SESSION['form_errors']['pembayaran_update'] = $err;
            $_SESSION['form_old']['pembayaran_update']    = $_POST;
            header("Location: {$this->baseUrl}index.php?r=pembayaran/edit&id=" . urlencode($id));
            exit;
        }

        if (!empty($_FILES['bukti_pembayaran']['name'])) {
            $d['bukti_pembayaran'] = $this->moveUpload($_FILES['bukti_pembayaran'], $id);
        }

        try {
            $ok = $this->model->update($id, $d);
            if ($ok) {
                audit_log('pembayaran.update', ['id' => $id] + $d);
                $_SESSION['success'] = 'Pembayaran berhasil diperbarui.';
                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'pembayaran_updated', [
                    'id'        => $id,
                    'proyek_id' => $d['proyek_id_proyek'],
                    'jenis'     => $d['jenis_pembayaran'],
                    'actor_id'  => $actorId,
                ]);
            } else {
                $_SESSION['error'] = 'Tidak ada perubahan / gagal update.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal memperbarui pembayaran: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=pembayaran");
        exit;
    }

    /** POST /pembayaran/delete */
    public function delete(): void
    {
        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }
        $id = trim($_POST['hapus_id'] ?? '');
        if ($id === '') {
            $_SESSION['error'] = 'ID tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }
        try {
            $ok = $this->model->delete($id);
            audit_log('pembayaran.delete', ['id' => $id]);
            $_SESSION['success'] = $ok ? 'Pembayaran dihapus.' : 'Gagal menghapus.';
            $actorId = (string)($_SESSION['user']['id'] ?? '');
            notif_event($this->pdo, 'pembayaran_deleted', [
                'id'       => $id,
                'actor_id' => $actorId,
            ]);
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menghapus: ' . $e->getMessage();
        }
        header("Location: {$this->baseUrl}index.php?r=pembayaran");
        exit;
    }

    /** GET /pembayaran/export (opsional) */
    public function exportCSV(): void
    {
        $search = trim($_GET['search'] ?? '');
        $rows = $this->model->exportRows($search);

        $fn = 'pembayaran_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$fn\"");
        $out = fopen('php://output', 'w');
        fputcsv($out, array_keys($rows[0] ?? [
            'ID',
            'Proyek',
            'Jenis',
            'Sub Total',
            'Pajak',
            'Total',
            'Jatuh Tempo',
            'Tanggal Bayar',
            'Status'
        ]));
        foreach ($rows as $r) fputcsv($out, $r);
        fclose($out);
        exit;
    }

    // ==== upload helpers ====
    private function validateUpload(array $f, ?string &$msg): bool
    {
        $msg = null;
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $msg = 'Gagal mengunggah file.';
            return false;
        }
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'heic'], true)) {
            $msg = 'Hanya JPG/JPEG/PNG/HEIC.';
            return false;
        }
        if ($f['size'] > 5 * 1024 * 1024) {
            $msg = 'Maksimal 5MB.';
            return false;
        }
        return true;
    }

    private function moveUpload(array $f, string $id): string
    {
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $dir = __DIR__ . '/../uploads/bukti';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        $name = 'BKT_' . preg_replace('~[^A-Za-z0-9]~', '', $id) . '_' . time() . '.' . $ext;
        $abs  = $dir . '/' . $name;
        if (!move_uploaded_file($f['tmp_name'], $abs)) {
            throw new RuntimeException('Gagal memindahkan file.');
        }
        // path relatif utk disimpan
        return 'uploads/bukti/' . $name;
    }
}
