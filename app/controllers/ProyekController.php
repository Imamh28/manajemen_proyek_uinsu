<?php
// app/controllers/ProyekController.php

require_once __DIR__ . '/../models/ProyekModel.php';
require_once __DIR__ . '/../utils/csrf.php';
require_once __DIR__ . '/../utils/roles.php';
require_once __DIR__ . '/../middleware/authorize.php';
require_once __DIR__ . '/../helpers/Audit.php';
require_once __DIR__ . '/../helpers/Notify.php';

class ProyekController
{
    private ProyekModel $model;

    private array $statusEnum = ['Menunggu', 'Berjalan', 'Selesai', 'Dibatalkan'];

    // ✅ Mandor dianggap bebas jika proyek selesai ATAU dibatalkan
    private array $doneStatusesForMandor = ['Selesai', 'Dibatalkan'];

    public function __construct(private PDO $pdo, private string $baseUrl)
    {
        $this->model   = new ProyekModel($pdo);
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

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

        $kid = $this->currentKaryawanId();
        if ($kid !== '') {
            try {
                $st = $this->pdo->prepare("SELECT role_id_role FROM karyawan WHERE id_karyawan=:id LIMIT 1");
                $st->execute([':id' => $kid]);
                $role = (string)($st->fetchColumn() ?: '');
                if ($role !== '') return $role;
            } catch (Throwable $e) {
            }
        }

        return '';
    }

    private function currentKaryawanId(): string
    {
        $u = $_SESSION['user'] ?? [];
        $candidates = [
            $u['id_karyawan'] ?? null,
            $u['karyawan_id'] ?? null,
            $u['karyawan_id_karyawan'] ?? null,
            $u['employee_id'] ?? null,
            $u['id'] ?? null,
        ];

        foreach ($candidates as $c) {
            $id = (string)($c ?? '');
            if ($id === '') continue;

            try {
                $st = $this->pdo->prepare("SELECT 1 FROM karyawan WHERE id_karyawan = :id LIMIT 1");
                $st->execute([':id' => $id]);
                if ($st->fetchColumn()) return $id;
            } catch (Throwable $e) {
            }
        }
        return '';
    }

    private function viewRoleDir(): string
    {
        $roleId = $this->currentRoleId();
        return role_dir($roleId);
    }

    private function validateMandorAvailability(string $mandorId, ?string $exceptProyekId = null): ?string
    {
        if ($mandorId === '') return 'PIC Site wajib dipilih.';

        $st = $this->pdo->prepare("SELECT role_id_role FROM karyawan WHERE id_karyawan = :id LIMIT 1");
        $st->execute([':id' => $mandorId]);
        $role = (string)($st->fetchColumn() ?: '');
        if ($role !== 'RL003') {
            return 'PIC Site harus user dengan role Mandor (RL003).';
        }

        $done = array_values(array_filter($this->doneStatusesForMandor, fn($s) => $s !== ''));
        if (!$done) $done = ['Selesai', 'Dibatalkan'];

        $ph = [];
        $bind = [':mid' => $mandorId];
        foreach ($done as $i => $s) {
            $k = ":ds{$i}";
            $ph[] = $k;
            $bind[$k] = $s;
        }

        $sql = "SELECT 1
                  FROM proyek
                 WHERE karyawan_id_pic_site = :mid
                   AND status NOT IN (" . implode(',', $ph) . ")";
        if ($exceptProyekId) {
            $sql .= " AND id_proyek <> :pid";
            $bind[':pid'] = $exceptProyekId;
        }
        $sql .= " LIMIT 1";

        $st2 = $this->pdo->prepare($sql);
        $st2->execute($bind);
        if ($st2->fetchColumn()) {
            return 'Mandor ini masih ditugaskan pada proyek aktif lain. Pilih mandor lain.';
        }

        return null;
    }

    public function index(): void
    {
        require_roles(['RL001', 'RL002'], $this->baseUrl);

        $search     = trim($_GET['search'] ?? '');
        $rows       = $this->model->all($search);
        $klienList  = $this->model->clients();
        $mandorList = $this->model->mandorAvailable(null, $this->doneStatusesForMandor);

        $BASE_URL   = $this->baseUrl;
        $proyek     = $rows;
        $statusEnum = $this->statusEnum;

        $EXISTING_IDS_JSON   = json_encode($this->model->existingIds(), JSON_UNESCAPED_UNICODE);
        $EXISTING_NAMES_JSON = json_encode($this->model->existingNames(), JSON_UNESCAPED_UNICODE);

        $NEXT_QUOTATION = $this->model->generateQuotationCode();

        $roleDir = $this->viewRoleDir();
        if ($roleDir === 'admin') {
            include __DIR__ . '/../views/admin/proyek/main.php';
        } elseif ($roleDir === 'projek_manajer') {
            include __DIR__ . '/../views/projek_manajer/proyek/main.php';
        } else {
            mark_forbidden();
            include __DIR__ . '/../views/error/403.php';
        }
    }

    public function store(): void
    {
        require_roles(['RL001', 'RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=proyek");
            exit;
        }

        $picSales = $this->currentKaryawanId();
        if ($picSales === '') {
            $_SESSION['error'] = 'Tidak bisa mendeteksi PIC Sales (id_karyawan valid) dari session.';
            header("Location: {$this->baseUrl}index.php?r=proyek");
            exit;
        }

        $d = [
            'id_proyek'             => strtoupper(trim($_POST['id_proyek'] ?? '')),
            'nama_proyek'           => trim($_POST['nama_proyek'] ?? ''),
            'deskripsi'             => trim($_POST['deskripsi'] ?? ''),
            'total_biaya_proyek'    => trim($_POST['total_biaya_proyek'] ?? ''),
            'alamat'                => trim($_POST['alamat'] ?? ''),
            'tanggal_mulai'         => trim($_POST['tanggal_mulai'] ?? ''),
            'tanggal_selesai'       => trim($_POST['tanggal_selesai'] ?? ''),
            'klien_id_klien'        => trim($_POST['klien_id_klien'] ?? ''),
            'karyawan_id_pic_site'  => trim($_POST['karyawan_id_pic_site'] ?? ''),

            'status'                => 'Menunggu',
            'karyawan_id_pic_sales' => $picSales,
            'quotation'             => $this->model->generateQuotationCode(),
            'gambar_kerja'          => null,
        ];

        $err = $this->validate($d, null);

        if ($this->model->existsId($d['id_proyek']))     $err['id_proyek']   = 'ID proyek sudah digunakan.';
        if ($this->model->existsName($d['nama_proyek'])) $err['nama_proyek'] = 'Nama proyek sudah digunakan.';

        $mandorErr = $this->validateMandorAvailability($d['karyawan_id_pic_site'], null);
        if ($mandorErr) $err['karyawan_id_pic_site'] = $mandorErr;

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
                audit_log('proyek.store', [
                    'id' => $d['id_proyek'],
                    'nama' => $d['nama_proyek'],
                    'quo' => $d['quotation'],
                    'pic_sales' => $d['karyawan_id_pic_sales'],
                    'pic_site' => $d['karyawan_id_pic_site'],
                ]);

                $_SESSION['success'] = 'Proyek berhasil ditambahkan.';

                notif_event($this->pdo, 'proyek_created', [
                    'proyek_id' => $d['id_proyek'],
                    'actor_id'  => $picSales,
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

    public function editForm(): void
    {
        require_roles(['RL001', 'RL002'], $this->baseUrl);

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

        $klienList  = $this->model->clients();
        $mandorList = $this->model->mandorAvailable($row['karyawan_id_pic_site'] ?? null, $this->doneStatusesForMandor);

        $BASE_URL   = $this->baseUrl;
        $statusEnum = $this->statusEnum;
        $proyek     = $row;

        $EXISTING_NAMES_JSON = json_encode($this->model->existingNames(), JSON_UNESCAPED_UNICODE);
        $CURRENT_NAME        = (string)($row['nama_proyek'] ?? '');

        $roleDir = $this->viewRoleDir();
        if ($roleDir === 'admin') {
            include __DIR__ . '/../views/admin/proyek/edit.php';
        } elseif ($roleDir === 'projek_manajer') {
            include __DIR__ . '/../views/projek_manajer/proyek/edit.php';
        } else {
            mark_forbidden();
            include __DIR__ . '/../views/error/403.php';
        }
    }

    public function update(): void
    {
        require_roles(['RL001', 'RL002'], $this->baseUrl);

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

        $current = $this->model->find($id);
        if (!$current) {
            $_SESSION['error'] = 'Data tidak ditemukan.';
            header("Location: {$this->baseUrl}index.php?r=proyek");
            exit;
        }

        $roleId  = $this->currentRoleId();
        $isAdmin = ($roleId === 'RL001');

        $d = [
            'nama_proyek'          => trim($_POST['nama_proyek'] ?? ''),
            'deskripsi'            => trim($_POST['deskripsi'] ?? ''),
            'total_biaya_proyek'   => trim($_POST['total_biaya_proyek'] ?? ''),
            'alamat'               => trim($_POST['alamat'] ?? ''),
            'tanggal_mulai'        => trim($_POST['tanggal_mulai'] ?? ''),
            'tanggal_selesai'      => trim($_POST['tanggal_selesai'] ?? ''),
            'klien_id_klien'       => trim($_POST['klien_id_klien'] ?? ''),
            'karyawan_id_pic_site' => trim($_POST['karyawan_id_pic_site'] ?? ''),
            'gambar_kerja'         => null,

            'status'               => $isAdmin ? trim($_POST['status'] ?? '') : (string)($current['status'] ?? 'Menunggu'),
        ];

        $err = $this->validate($d, $id);

        if ($this->model->existsName($d['nama_proyek'], $id)) {
            $err['nama_proyek'] = 'Nama proyek sudah digunakan.';
        }

        $mandorErr = $this->validateMandorAvailability($d['karyawan_id_pic_site'], $id);
        if ($mandorErr) $err['karyawan_id_pic_site'] = $mandorErr;

        $upload = $this->handleUpload('gambar_kerja', true);
        if (isset($upload['error'])) {
            $err['gambar_kerja'] = $upload['error'];
        } else {
            $d['gambar_kerja'] = $upload['path'] ?? ($current['gambar_kerja'] ?? null);
        }

        if ($err) {
            $_SESSION['form_errors']['proyek_update'] = $err;
            $_SESSION['form_old']['proyek_update']    = array_merge($d, ['id_proyek' => $id]);
            header("Location: {$this->baseUrl}index.php?r=proyek/edit&id=" . urlencode($id));
            exit;
        }

        try {
            if ($this->model->update($id, $d)) {
                audit_log('proyek.update', ['id' => $id, 'nama' => $d['nama_proyek']]);
                $_SESSION['success'] = 'Proyek berhasil diperbarui.';

                // ✅ jika admin set status Selesai/Dibatalkan → kosongkan current_tahapan_id
                if ($isAdmin && in_array($d['status'], ['Selesai', 'Dibatalkan'], true)) {
                    $st = $this->pdo->prepare("UPDATE proyek SET current_tahapan_id = NULL WHERE id_proyek = :p");
                    $st->execute([':p' => $id]);
                }

                $actorKid = $this->currentKaryawanId();
                notif_event($this->pdo, 'proyek_updated', [
                    'proyek_id' => $id,
                    'actor_id'  => $actorKid !== '' ? $actorKid : '',
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

    public function delete(): void
    {
        // ✅ Admin & Project Manager sama-sama kena guard
        require_roles(['RL001', 'RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
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

        // mode
        $force = (($_POST['force'] ?? '') === '1');

        // opsi force delete (checkbox)
        $delJadwal    = (($_POST['del_jadwal'] ?? '') === '1');
        $delBayar     = (($_POST['del_pembayaran'] ?? '') === '1');
        $delRequests  = (($_POST['del_requests'] ?? '') === '1');

        try {
            $rel = $this->model->relationsSummary($id);
            $hasRel = ($rel['jadwal'] + $rel['pembayaran'] + $rel['tahapan_requests']) > 0;

            if ($hasRel && !$force) {
                // simpan detail untuk ditampilkan sebagai alert + tombol "force delete"
                $_SESSION['delete_blocked_proyek'] = [
                    'id' => $id,
                    'rel' => $rel,
                ];

                $_SESSION['error'] =
                    'Proyek tidak bisa dihapus karena memiliki relasi: ' .
                    'Jadwal (' . $rel['jadwal'] . '), ' .
                    'Pembayaran (' . $rel['pembayaran'] . '), ' .
                    'Permintaan Tahapan (' . $rel['tahapan_requests'] . '). ' .
                    'Gunakan opsi "Hapus Proyek + Data Terkait" jika memang ingin memaksa.';

                header("Location: {$this->baseUrl}index.php?r=proyek");
                exit;
            }

            if ($hasRel && $force) {
                // kalau ada relasi, tapi user tidak centang penghapusan relasi itu -> tolak
                $missing = [];
                if ($rel['jadwal'] > 0 && !$delJadwal) $missing[] = 'jadwal';
                if ($rel['pembayaran'] > 0 && !$delBayar) $missing[] = 'pembayaran';
                if ($rel['tahapan_requests'] > 0 && !$delRequests) $missing[] = 'tahapan_update_requests';

                if ($missing) {
                    $_SESSION['error'] =
                        'Gagal force delete: masih ada relasi yang belum Anda izinkan untuk dihapus (' .
                        implode(', ', $missing) . '). Centang semua relasi yang ada.';
                    header("Location: {$this->baseUrl}index.php?r=proyek");
                    exit;
                }

                $ok = $this->model->deleteCascade($id, [
                    'jadwal' => $delJadwal,
                    'pembayaran' => $delBayar,
                    'tahapan_requests' => $delRequests,
                ]);

                if ($ok) {
                    audit_log('proyek.delete_force', ['id' => $id, 'rel' => $rel]);
                    $_SESSION['success'] = 'Proyek berhasil dihapus BESERTA data terkaitnya.';

                    $actorKid = $this->currentKaryawanId();
                    notif_event($this->pdo, 'proyek_deleted', [
                        'proyek_id' => $id,
                        'actor_id'  => $actorKid !== '' ? $actorKid : '',
                    ]);
                } else {
                    $_SESSION['error'] = 'Gagal menghapus proyek (force).';
                }

                header("Location: {$this->baseUrl}index.php?r=proyek");
                exit;
            }

            // no relation -> delete normal
            if ($this->model->delete($id)) {
                audit_log('proyek.delete', ['id' => $id]);
                $_SESSION['success'] = 'Proyek berhasil dihapus.';

                $actorKid = $this->currentKaryawanId();
                notif_event($this->pdo, 'proyek_deleted', [
                    'proyek_id' => $id,
                    'actor_id'  => $actorKid !== '' ? $actorKid : '',
                ]);
            } else {
                $_SESSION['error'] = 'Gagal menghapus proyek.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menghapus proyek: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=proyek");
        exit;
    }

    public function setStatus(): void
    {
        require_roles(['RL001'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=proyek");
            exit;
        }

        $id = trim($_POST['id_proyek'] ?? '');
        $status = trim($_POST['status'] ?? '');

        if ($id === '' || $status === '') {
            $_SESSION['error'] = 'Permintaan tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=proyek");
            exit;
        }

        if (!in_array($status, $this->statusEnum, true)) {
            $_SESSION['error'] = 'Status tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=proyek");
            exit;
        }

        try {
            if ($this->model->setStatus($id, $status)) {
                audit_log('proyek.setStatus', ['id' => $id, 'status' => $status]);
                $_SESSION['success'] = "Status proyek berhasil diubah menjadi {$status}.";

                if (in_array($status, ['Selesai', 'Dibatalkan'], true)) {
                    $st = $this->pdo->prepare("UPDATE proyek SET current_tahapan_id = NULL WHERE id_proyek = :p");
                    $st->execute([':p' => $id]);
                }
            } else {
                $_SESSION['error'] = 'Gagal mengubah status.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal mengubah status: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=proyek/edit&id=" . urlencode($id));
        exit;
    }

    public function exportCSV(): void
    {
        require_roles(['RL001', 'RL002'], $this->baseUrl);

        $search = trim($_GET['search'] ?? '');
        $rows   = $this->model->exportRows($search);

        $filename = 'proyek-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, ['ID', 'Nama Proyek', 'Klien', 'Total Biaya', 'Status', 'PIC Sales', 'PIC Site']);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id_proyek'] ?? '',
                $r['nama_proyek'] ?? '',
                $r['nama_klien'] ?? '',
                $r['total_biaya_proyek'] ?? '',
                $r['status'] ?? '',
                $r['nama_sales'] ?? '',
                $r['nama_site'] ?? '',
            ]);
        }
        fclose($out);

        audit_log('proyek.export', ['count' => count($rows), 'search' => $search]);
        exit;
    }

    private function validate(array &$d, ?string $id = null): array
    {
        $errors = [];

        $required = [
            'nama_proyek',
            'deskripsi',
            'total_biaya_proyek',
            'alamat',
            'tanggal_mulai',
            'tanggal_selesai',
            'status',
            'klien_id_klien',
            'karyawan_id_pic_site',
        ];
        if (!$id) array_unshift($required, 'id_proyek');

        foreach ($required as $k) {
            if (($d[$k] ?? '') === '') $errors[$k] = 'Wajib diisi.';
        }

        if (!$id && ($d['id_proyek'] ?? '') !== '' && !str_starts_with($d['id_proyek'], 'PRJ')) {
            $errors['id_proyek'] = 'ID harus diawali "PRJ".';
        }

        if (($d['nama_proyek'] ?? '') !== '' && mb_strlen($d['nama_proyek']) > 45) {
            $errors['nama_proyek'] = 'Nama maksimal 45 karakter.';
        }

        if (($d['total_biaya_proyek'] ?? '') !== '') {
            $digits = preg_replace('/\D+/', '', (string)$d['total_biaya_proyek']);
            if ($digits === '' || !ctype_digit($digits)) {
                $errors['total_biaya_proyek'] = 'Total biaya harus angka.';
            } else {
                $d['total_biaya_proyek'] = $digits;
            }
        }

        foreach (['tanggal_mulai', 'tanggal_selesai'] as $t) {
            $v = $d[$t] ?? '';
            if ($v !== '') {
                $dt = DateTime::createFromFormat('Y-m-d', $v);
                $ok = $dt && $dt->format('Y-m-d') === $v;
                if (!$ok) $errors[$t] = 'Tanggal tidak valid.';
            }
        }

        if (
            empty($errors['tanggal_mulai']) && empty($errors['tanggal_selesai'])
            && ($d['tanggal_mulai'] ?? '') !== '' && ($d['tanggal_selesai'] ?? '') !== ''
        ) {
            if ($d['tanggal_selesai'] < $d['tanggal_mulai']) {
                $errors['tanggal_selesai'] = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
            }
        }

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
        if ($f['error'] !== UPLOAD_ERR_OK) return ['error' => 'Upload gagal.'];

        $allowedExt = ['jpg', 'jpeg', 'png', 'heic'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) return ['error' => 'Tipe file harus JPG, JPEG, PNG, atau HEIC.'];

        $mime = mime_content_type($f['tmp_name']) ?: '';
        $okMime = str_contains($mime, 'image/');
        if (!$okMime) return ['error' => 'Tipe file tidak dikenali.'];

        $dir = __DIR__ . '/../uploads/proyek';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);

        $basename = 'proyek_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        $destAbs  = $dir . '/' . $basename;

        if (!move_uploaded_file($f['tmp_name'], $destAbs)) return ['error' => 'Gagal menyimpan file.'];

        return ['path' => 'uploads/proyek/' . $basename];
    }
}
