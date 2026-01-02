<?php
// app/controllers/PembayaranController.php

require_once __DIR__ . '/../utils/roles.php';
require_once __DIR__ . '/../middleware/authorize.php';
require_once __DIR__ . '/../helpers/Audit.php';
require_once __DIR__ . '/../utils/csrf.php';
require_once __DIR__ . '/../helpers/Notify.php';

class PembayaranController
{
    private PembayaranModel $model;

    public function __construct(private PDO $pdo, private string $baseUrl)
    {
        require_once __DIR__ . '/../models/PembayaranModel.php';
        $this->model = new PembayaranModel($this->pdo);

        $this->baseUrl = rtrim($this->baseUrl, '/') . '/';
    }

    /** Ambil roleId dari session (robust) */
    private function currentRoleId(): string
    {
        $u = $_SESSION['user'] ?? [];
        $candidates = [
            $u['role_id'] ?? null,
            $u['role_id_role'] ?? null,
            $u['role'] ?? null,
            $u['id_role'] ?? null,
        ];

        foreach ($candidates as $c) {
            $id = (string)($c ?? '');
            if ($id !== '') return $id;
        }
        return '';
    }

    /** Ambil id_karyawan VALID dari session */
    private function currentKaryawanId(): string
    {
        $u = $_SESSION['user'] ?? [];
        $candidates = [
            $u['id_karyawan'] ?? null,
            $u['karyawan_id'] ?? null,
            $u['karyawan_id_karyawan'] ?? null,
            $u['employee_id'] ?? null,
            $u['id'] ?? null, // fallback terakhir
        ];

        foreach ($candidates as $c) {
            $id = (string)($c ?? '');
            if ($id === '') continue;

            try {
                $st = $this->pdo->prepare("SELECT 1 FROM karyawan WHERE id_karyawan = :id LIMIT 1");
                $st->execute([':id' => $id]);
                if ($st->fetchColumn()) return $id;
            } catch (Throwable $e) {
                // lanjut kandidat berikutnya
            }
        }
        return '';
    }

    /** Ambil daftar proyek yang PIC Sales-nya = user login */
    private function myProjectIds(string $karyawanId): array
    {
        if ($karyawanId === '') return [];
        $st = $this->pdo->prepare("SELECT id_proyek FROM proyek WHERE karyawan_id_pic_sales = :kid");
        $st->execute([':kid' => $karyawanId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_values(array_unique(array_map(fn($r) => (string)$r['id_proyek'], $rows)));
    }

    /** Cek apakah proyek ini milik user (PIC Sales) */
    private function assertProjectOwnedByMe(string $proyekId, string $karyawanId): bool
    {
        if ($proyekId === '' || $karyawanId === '') return false;
        $st = $this->pdo->prepare("SELECT 1 FROM proyek WHERE id_proyek = :p AND karyawan_id_pic_sales = :k LIMIT 1");
        $st->execute([':p' => $proyekId, ':k' => $karyawanId]);
        return (bool)$st->fetchColumn();
    }

    private function rupiah(int $n): string
    {
        return 'Rp ' . number_format($n, 0, ',', '.');
    }

    private function maxSubFromRemaining(int $remainingTotal, float $taxRate = 0.10): int
    {
        $sub = (int) floor($remainingTotal / (1.0 + $taxRate));
        for ($i = 0; $i < 5; $i++) {
            $pajak = (int) round($sub * $taxRate);
            $tot = $sub + $pajak;
            if ($tot <= $remainingTotal) return $sub;
            $sub--;
            if ($sub <= 0) return 0;
        }
        return max(0, $sub);
    }

    /** GET /pembayaran : list + form tambah */
    public function index(): void
    {
        require_roles(['RL001', 'RL002'], $this->baseUrl);

        $karyawanId = $this->currentKaryawanId();
        if ($karyawanId === '') {
            $_SESSION['error'] = 'Tidak bisa mendeteksi id_karyawan valid dari session.';
            header("Location: {$this->baseUrl}index.php?r=dashboard");
            exit;
        }

        $q = trim($_GET['search'] ?? '');

        // list pembayaran scoped: hanya proyek milik user (PIC Sales)
        $pembayarans = $this->model->all($q, $karyawanId);

        // proyek + meta (total proyek & akumulasi pembayaran) untuk live validation
        $proyekList = $this->model->projectsWithMeta($karyawanId);

        // jika hanya 1 proyek → tidak perlu select
        $ONLY_PROJECT = (count($proyekList) === 1) ? $proyekList[0] : null;

        // meta JSON untuk JS
        $metaMap = [];
        foreach ($proyekList as $p) {
            $pid = (string)($p['id_proyek'] ?? '');
            if ($pid === '') continue;
            $metaMap[$pid] = [
                'total' => (int)($p['total_biaya_proyek'] ?? 0),
                'paid'  => (int)round((float)($p['paid_total'] ?? 0)),
                'name'  => (string)($p['nama_proyek'] ?? ''),
            ];
        }
        $PROJECT_META_JSON = json_encode($metaMap, JSON_UNESCAPED_UNICODE);

        $BASE_URL   = $this->baseUrl;
        $jenisEnum  = ['DP', 'Termin', 'Pelunasan'];
        $statusEnum = ['Belum Lunas', 'Lunas'];

        $EXISTING_IDS_JSON = json_encode(
            array_values(array_unique(array_map('strval', $this->model->existingIds()))),
            JSON_UNESCAPED_UNICODE
        );

        $roleDir = role_dir($this->currentRoleId());
        include __DIR__ . "/../views/{$roleDir}/pembayaran/main.php";
    }

    /** POST /pembayaran/store */
    public function store(): void
    {
        require_roles(['RL001', 'RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

        $karyawanId = $this->currentKaryawanId();
        if ($karyawanId === '') {
            $_SESSION['error'] = 'Tidak bisa mendeteksi id_karyawan valid dari session.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

        $digits = fn(string $s) => preg_replace('~\D+~', '', $s);

        $d = [
            'id_pem_bayaran'      => trim($_POST['id_pem_bayaran'] ?? ''),
            'jenis_pembayaran'    => trim($_POST['jenis_pembayaran'] ?? ''),
            'sub_total'           => $digits($_POST['sub_total'] ?? ''),
            'pajak_pembayaran'    => $digits($_POST['pajak_pembayaran'] ?? ''),
            'total_pembayaran'    => $digits($_POST['total_pembayaran'] ?? ''),
            'tanggal_jatuh_tempo' => trim($_POST['tanggal_jatuh_tempo'] ?? ''),
            'tanggal_bayar'       => trim($_POST['tanggal_bayar'] ?? ''),
            'status_pembayaran'   => trim($_POST['status_pembayaran'] ?? ''),
            'proyek_id_proyek'    => trim($_POST['proyek_id_proyek'] ?? ''),
            'bukti_pembayaran'    => null,
        ];

        // FORCE proyek jika user hanya punya 1 proyek
        $allowedProjectIds = $this->myProjectIds($karyawanId);
        if (count($allowedProjectIds) === 1) {
            $d['proyek_id_proyek'] = $allowedProjectIds[0];
        }

        // SECURITY: pastikan proyek milik user (PIC Sales)
        if (!$this->assertProjectOwnedByMe($d['proyek_id_proyek'], $karyawanId)) {
            $_SESSION['error'] = 'Akses ditolak: hanya PIC Sales proyek yang boleh input pembayaran.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

        // hitung ulang (server trust)
        $sub   = (int)($d['sub_total'] ?: 0);
        $pajak = (int) round($sub * 0.10);
        $total = $sub + $pajak;
        $d['pajak_pembayaran'] = (string)$pajak;
        $d['total_pembayaran'] = (string)$total;

        $err = [];

        // VALIDASI batas sisa tagihan (TOTAL sudah termasuk pajak)
        $projectTotal = $this->model->projectTotalBiaya($d['proyek_id_proyek']);
        $sumExisting  = $this->model->sumTotalPembayaranByProyek($d['proyek_id_proyek'], null);
        $remaining    = $projectTotal - $sumExisting;

        if ($projectTotal <= 0) {
            $err['proyek_id_proyek'] = 'Total biaya proyek belum diset / tidak valid.';
        } elseif ($remaining <= 0) {
            $err['sub_total'] = 'Proyek ini sudah mencapai total biaya (sudah lunas). Total proyek: ' . $this->rupiah($projectTotal) . '.';
        } elseif ($total > $remaining) {
            $maxSub = $this->maxSubFromRemaining($remaining, 0.10);
            $err['sub_total'] = 'Nominal pembayaran melebihi sisa tagihan proyek. '
                . 'Sisa (termasuk pajak): ' . $this->rupiah($remaining) . '. '
                . 'Maks sub total (sebelum pajak 10%): ' . $this->rupiah($maxSub) . '.';
        }

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

        if ($d['id_pem_bayaran'] !== '') {
            if (!str_starts_with($d['id_pem_bayaran'], 'PAY')) {
                $err['id_pem_bayaran'] = 'ID harus diawali "PAY".';
            } elseif ($this->model->existsId($d['id_pem_bayaran'])) {
                $err['id_pem_bayaran'] = 'ID pembayaran sudah digunakan.';
            }
        }

        if ($sub <= 0) $err['sub_total'] = $err['sub_total'] ?? 'Sub total harus angka > 0.';

        $isDate = function (string $s): bool {
            $dt = DateTime::createFromFormat('Y-m-d', $s);
            return $dt && $dt->format('Y-m-d') === $s;
        };
        if ($d['tanggal_jatuh_tempo'] && !$isDate($d['tanggal_jatuh_tempo'])) $err['tanggal_jatuh_tempo'] = 'Tanggal tidak valid.';
        if ($d['tanggal_bayar'] && !$isDate($d['tanggal_bayar'])) $err['tanggal_bayar'] = 'Tanggal tidak valid.';

        // bukti wajib saat create
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
        $buktiPath = $this->moveUpload($_FILES['bukti_pembayaran'], $d['id_pem_bayaran']);
        $d['bukti_pembayaran'] = $buktiPath;

        try {
            $ok = $this->model->create($d);
            if ($ok) {
                audit_log('pembayaran.create', [
                    'id' => $d['id_pem_bayaran'],
                    'proyek' => $d['proyek_id_proyek'],
                    'jenis' => $d['jenis_pembayaran'],
                    'total' => $d['total_pembayaran']
                ]);
                $_SESSION['success'] = 'Pembayaran berhasil ditambahkan.';

                notif_event($this->pdo, 'pembayaran_created', [
                    'id'        => $d['id_pem_bayaran'],
                    'proyek_id' => $d['proyek_id_proyek'],
                    'jenis'     => $d['jenis_pembayaran'],
                    'actor_id'  => $karyawanId,
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
        require_roles(['RL001', 'RL002'], $this->baseUrl);

        $karyawanId = $this->currentKaryawanId();
        if ($karyawanId === '') {
            $_SESSION['error'] = 'Tidak bisa mendeteksi id_karyawan valid dari session.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

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

        // SECURITY: pastikan pembayaran ini milik proyek user (PIC Sales)
        $pid = (string)($row['proyek_id_proyek'] ?? '');
        if (!$this->assertProjectOwnedByMe($pid, $karyawanId)) {
            mark_forbidden();
            include __DIR__ . '/../views/error/403.php';
            return;
        }

        // proyek + meta untuk JS
        $proyekList = $this->model->projectsWithMeta($karyawanId);
        $ONLY_PROJECT = (count($proyekList) === 1) ? $proyekList[0] : null;

        $metaMap = [];
        foreach ($proyekList as $p) {
            $ppid = (string)($p['id_proyek'] ?? '');
            if ($ppid === '') continue;
            $metaMap[$ppid] = [
                'total' => (int)($p['total_biaya_proyek'] ?? 0),
                'paid'  => (int)round((float)($p['paid_total'] ?? 0)),
                'name'  => (string)($p['nama_proyek'] ?? ''),
            ];
        }
        $PROJECT_META_JSON = json_encode($metaMap, JSON_UNESCAPED_UNICODE);

        $BASE_URL   = $this->baseUrl;
        $pembayaran = $row;
        $jenisEnum  = ['DP', 'Termin', 'Pelunasan'];
        $statusEnum = ['Belum Lunas', 'Lunas'];

        $__updErr = $_SESSION['form_errors']['pembayaran_update'] ?? [];
        $__updOld = $_SESSION['form_old']['pembayaran_update'] ?? [];
        unset($_SESSION['form_errors']['pembayaran_update'], $_SESSION['form_old']['pembayaran_update']);

        $roleDir = role_dir($this->currentRoleId());
        include __DIR__ . "/../views/{$roleDir}/pembayaran/edit.php";
    }

    /** POST /pembayaran/update */
    public function update(): void
    {
        require_roles(['RL001', 'RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

        $karyawanId = $this->currentKaryawanId();
        if ($karyawanId === '') {
            $_SESSION['error'] = 'Tidak bisa mendeteksi id_karyawan valid dari session.';
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

        // SECURITY: pastikan pembayaran ini milik user
        $currentPid = (string)($row['proyek_id_proyek'] ?? '');
        if (!$this->assertProjectOwnedByMe($currentPid, $karyawanId)) {
            mark_forbidden();
            include __DIR__ . '/../views/error/403.php';
            return;
        }

        $digits = fn(string $s) => preg_replace('~\D+~', '', $s);

        $d = [
            'jenis_pembayaran'    => trim($_POST['jenis_pembayaran'] ?? ''),
            'sub_total'           => $digits($_POST['sub_total'] ?? ''),
            'pajak_pembayaran'    => $digits($_POST['pajak_pembayaran'] ?? ''),
            'total_pembayaran'    => $digits($_POST['total_pembayaran'] ?? ''),
            'tanggal_jatuh_tempo' => trim($_POST['tanggal_jatuh_tempo'] ?? ''),
            'tanggal_bayar'       => trim($_POST['tanggal_bayar'] ?? ''),
            'status_pembayaran'   => trim($_POST['status_pembayaran'] ?? ''),
            'proyek_id_proyek'    => trim($_POST['proyek_id_proyek'] ?? ''),
            'bukti_pembayaran'    => $row['bukti_pembayaran'],
        ];

        // FORCE proyek jika user hanya punya 1 proyek
        $allowedProjectIds = $this->myProjectIds($karyawanId);
        if (count($allowedProjectIds) === 1) {
            $d['proyek_id_proyek'] = $allowedProjectIds[0];
        }

        // SECURITY: user tidak boleh memindah ke proyek yang bukan miliknya
        if (!$this->assertProjectOwnedByMe($d['proyek_id_proyek'], $karyawanId)) {
            $_SESSION['error'] = 'Akses ditolak: hanya PIC Sales proyek yang boleh mengubah pembayaran.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran/edit&id=" . urlencode($id));
            exit;
        }

        // recalc server
        $sub   = (int)($d['sub_total'] ?: 0);
        $pajak = (int) round($sub * 0.10);
        $total = $sub + $pajak;
        $d['pajak_pembayaran'] = (string)$pajak;
        $d['total_pembayaran'] = (string)$total;

        $err = [];

        // VALIDASI batas sisa tagihan (EXCLUDE record ini)
        $projectTotal = $this->model->projectTotalBiaya($d['proyek_id_proyek']);
        $sumExisting  = $this->model->sumTotalPembayaranByProyek($d['proyek_id_proyek'], $id);
        $remaining    = $projectTotal - $sumExisting;

        if ($projectTotal <= 0) {
            $err['proyek_id_proyek'] = 'Total biaya proyek belum diset / tidak valid.';
        } elseif ($remaining <= 0) {
            $err['sub_total'] = 'Proyek ini sudah mencapai total biaya (sudah lunas). Total proyek: ' . $this->rupiah($projectTotal) . '.';
        } elseif ($total > $remaining) {
            $maxSub = $this->maxSubFromRemaining($remaining, 0.10);
            $err['sub_total'] = 'Nominal pembayaran melebihi sisa tagihan proyek. '
                . 'Sisa (termasuk pajak): ' . $this->rupiah($remaining) . '. '
                . 'Maks sub total (sebelum pajak 10%): ' . $this->rupiah($maxSub) . '.';
        }

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

        $isDate = function (string $s): bool {
            $dt = DateTime::createFromFormat('Y-m-d', $s);
            return $dt && $dt->format('Y-m-d') === $s;
        };
        if ($d['tanggal_jatuh_tempo'] && !$isDate($d['tanggal_jatuh_tempo'])) $err['tanggal_jatuh_tempo'] = 'Tanggal tidak valid.';
        if ($d['tanggal_bayar'] && !$isDate($d['tanggal_bayar'])) $err['tanggal_bayar'] = 'Tanggal tidak valid.';
        if ($sub <= 0) $err['sub_total'] = $err['sub_total'] ?? 'Sub total harus angka > 0.';

        // file opsional ganti
        if (!empty($_FILES['bukti_pembayaran']['name'])) {
            $ok = $this->validateUpload($_FILES['bukti_pembayaran'], $msg);
            if (!$ok) $err['bukti_pembayaran'] = $msg ?: 'File tidak valid.';
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
                audit_log('pembayaran.update', ['id' => $id, 'proyek' => $d['proyek_id_proyek']]);
                $_SESSION['success'] = 'Pembayaran berhasil diperbarui.';

                notif_event($this->pdo, 'pembayaran_updated', [
                    'id'        => $id,
                    'proyek_id' => $d['proyek_id_proyek'],
                    'jenis'     => $d['jenis_pembayaran'],
                    'actor_id'  => $karyawanId,
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
        require_roles(['RL001', 'RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

        $karyawanId = $this->currentKaryawanId();
        if ($karyawanId === '') {
            $_SESSION['error'] = 'Tidak bisa mendeteksi id_karyawan valid dari session.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

        $id = trim($_POST['hapus_id'] ?? '');
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

        $pid = (string)($row['proyek_id_proyek'] ?? '');
        if (!$this->assertProjectOwnedByMe($pid, $karyawanId)) {
            mark_forbidden();
            include __DIR__ . '/../views/error/403.php';
            return;
        }

        try {
            $ok = $this->model->delete($id);
            audit_log('pembayaran.delete', ['id' => $id, 'proyek' => $pid]);
            $_SESSION['success'] = $ok ? 'Pembayaran dihapus.' : 'Gagal menghapus.';

            notif_event($this->pdo, 'pembayaran_deleted', [
                'id'       => $id,
                'actor_id' => $karyawanId,
            ]);
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menghapus: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=pembayaran");
        exit;
    }

    public function exportCSV(): void
    {
        require_roles(['RL001', 'RL002'], $this->baseUrl);

        $karyawanId = $this->currentKaryawanId();
        if ($karyawanId === '') {
            $_SESSION['error'] = 'Tidak bisa mendeteksi id_karyawan valid dari session.';
            header("Location: {$this->baseUrl}index.php?r=pembayaran");
            exit;
        }

        $search = trim($_GET['search'] ?? '');
        $rows = $this->model->exportRows($search, $karyawanId);

        $fn = 'pembayaran_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$fn\"");
        header('Cache-Control: no-store, no-cache');

        $out = fopen('php://output', 'w');
        if (!empty($rows)) {
            fputcsv($out, array_keys($rows[0]));
            foreach ($rows as $r) fputcsv($out, $r);
        } else {
            fputcsv($out, ['Tidak ada data']);
        }
        fclose($out);
        exit;
    }

    // ==== upload helpers ====
    private function validateUpload(array $f, ?string &$msg): bool
    {
        $msg = null;

        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $msg = 'Gagal mengunggah file.';
            return false;
        }

        $ext = strtolower(pathinfo($f['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'heic'], true)) {
            $msg = 'Hanya JPG/JPEG/PNG/HEIC.';
            return false;
        }

        if (($f['size'] ?? 0) > 5 * 1024 * 1024) {
            $msg = 'Maksimal 5MB.';
            return false;
        }

        $mime = mime_content_type($f['tmp_name'] ?? '') ?: '';
        $okMime = str_contains($mime, 'jpeg') || str_contains($mime, 'png') || str_contains($mime, 'heic') || str_starts_with($mime, 'image/');
        if (!$okMime) {
            $msg = 'Tipe file tidak dikenali.';
            return false;
        }

        return true;
    }

    private function moveUpload(array $f, string $id): string
    {
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $dir = __DIR__ . '/../uploads/bukti';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);

        $safeId = preg_replace('~[^A-Za-z0-9]~', '', $id);
        $name = 'BKT_' . $safeId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;

        $abs  = $dir . '/' . $name;
        if (!move_uploaded_file($f['tmp_name'], $abs)) {
            throw new RuntimeException('Gagal memindahkan file.');
        }

        return 'uploads/bukti/' . $name;
    }
}
