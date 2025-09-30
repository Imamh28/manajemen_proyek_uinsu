<?php
// app/controllers/PenjadwalanController.php
require_once __DIR__ . '/../models/PenjadwalanModel.php';
require_once __DIR__ . '/../helpers/Audit.php';
require_once __DIR__ . '/../utils/csrf.php';
require_once __DIR__ . '/../helpers/Notify.php'; // role-aware notifications

class PenjadwalanController
{
    private PenjadwalanModel $model;

    public function __construct(private PDO $pdo, private string $baseUrl)
    {
        $this->model   = new PenjadwalanModel($pdo);
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    /** GET /penjadwalan?proyek=PRJ001 */
    public function index(): void
    {
        // HANYA PM
        require_roles(['RL002'], $this->baseUrl);

        $proyekId = trim($_GET['proyek'] ?? '');
        $projects = $this->model->projects();
        if ($proyekId === '' && !empty($projects)) {
            $proyekId = $projects[0]['id_proyek'];
        }

        $project    = $proyekId ? $this->model->projectDetail($proyekId) : null;
        $rows       = $proyekId ? $this->model->all($proyekId) : [];

        // hint: tahapan berikutnya (otomatis)
        $NEXT_TAHAP = $proyekId ? ($this->model->nextTahapanId($proyekId) ?? '-') : '-';

        // live validation (unik per proyek)
        $EXISTING_IDS_JSON = json_encode($this->model->existingIdsByProject($proyekId), JSON_UNESCAPED_UNICODE);

        $BASE_URL   = $this->baseUrl;
        $proyekId   = $proyekId;
        $projek     = $project;
        $jadwal     = $rows;
        $projectsDD = $projects;
        $NEXT_TAHAP = $NEXT_TAHAP;

        include __DIR__ . '/../views/projek_manajer/jadwal/main.php';
    }

    /** GET /penjadwalan/edit&id=JDL001 */
    public function editForm(): void
    {
        // HANYA PM
        require_roles(['RL002'], $this->baseUrl);

        $id  = trim($_GET['id'] ?? '');
        $row = $this->model->find($id);
        if (!$row) {
            $_SESSION['error'] = 'Data jadwal tidak ditemukan.';
            header("Location: {$this->baseUrl}index.php?r=penjadwalan");
            exit;
        }

        $project  = $this->model->projectDetail($row['proyek_id_proyek']);
        $BASE_URL = $this->baseUrl;
        $jadwal   = $row;
        $projek   = $project;

        include __DIR__ . '/../views/projek_manajer/jadwal/edit.php';
    }

    /** GET /penjadwalan/detailproyek&proyek=PRJxxx */
    public function detailProyek(): void
    {
        require_roles(['RL002'], $this->baseUrl);

        $pid    = trim($_GET['proyek'] ?? '');
        $projek = $pid ? $this->model->projectDetail($pid) : null;

        $BASE_URL = $this->baseUrl;
        include __DIR__ . '/../views/projek_manajer/detailproyek/main.php';
    }

    /** GET /penjadwalan/detailpembayaran&proyek=PRJxxx */
    public function detailPembayaran(): void
    {
        require_roles(['RL002'], $this->baseUrl);

        $pid        = trim($_GET['proyek'] ?? '');
        $projek     = $pid ? $this->model->projectDetail($pid) : null;
        // pakai nama method yang SUDAH ada di model kamu (paymentsByProject)
        $pembayaran = $pid ? $this->model->paymentsByProject($pid) : [];

        $BASE_URL = $this->baseUrl;
        include __DIR__ . '/../views/projek_manajer/detailpembayaran/main.php';
    }

    /** POST /penjadwalan/store */
    public function store(): void
    {
        // HANYA PM
        require_roles(['RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=penjadwalan");
            exit;
        }

        $d = [
            'id_jadwal'        => trim($_POST['id_jadwal'] ?? ''),
            'proyek_id_proyek' => trim($_POST['proyek_id_proyek'] ?? ''),
            'plan_mulai'       => trim($_POST['plan_mulai'] ?? ''),
            'plan_selesai'     => trim($_POST['plan_selesai'] ?? ''),
        ];

        $err = $this->validate($d);
        if ($this->model->existsId($d['id_jadwal'])) {
            $err['id_jadwal'] = 'ID Jadwal sudah dipakai.';
        }

        // Tahapan otomatis (TH01..TH06 yang belum dipakai)
        $autoTahap = $this->model->nextTahapanId($d['proyek_id_proyek']);
        if (!$autoTahap) {
            $err['id_jadwal'] = 'Semua tahapan untuk proyek ini sudah dibuat.';
        }

        if ($err) {
            $_SESSION['form_errors']['jadwal_store'] = $err;
            $_SESSION['form_old']['jadwal_store']    = $d;
            header("Location: {$this->baseUrl}index.php?r=penjadwalan&proyek=" . urlencode($d['proyek_id_proyek']));
            exit;
        }

        $d['durasi']                     = $this->model->diffDaysInclusive($d['plan_mulai'], $d['plan_selesai']);
        $d['daftar_tahapans_id_tahapan'] = $autoTahap; // inject otomatis

        try {
            if ($this->model->create($d)) {
                audit_log('jadwal.store', ['id' => $d['id_jadwal'], 'proyek' => $d['proyek_id_proyek']]);
                $_SESSION['success'] = 'Jadwal berhasil ditambahkan (tahapan otomatis: ' . $autoTahap . ').';

                // Notifikasi jadwal (Admin & Mandor = info-only; PM = actionable)
                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'jadwal_created', [
                    'proyek_id' => $d['proyek_id_proyek'],
                    'id_jadwal' => $d['id_jadwal'],
                    'actor_id'  => $actorId,
                ]);
            } else {
                $_SESSION['error'] = 'Gagal menambah jadwal.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menambah jadwal: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=penjadwalan&proyek=" . urlencode($d['proyek_id_proyek']));
        exit;
    }

    /** POST /penjadwalan/update */
    public function update(): void
    {
        // HANYA PM
        require_roles(['RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=penjadwalan");
            exit;
        }

        $id  = trim($_POST['id_jadwal'] ?? '');
        $pid = trim($_POST['proyek_id_proyek'] ?? '');

        $d = [
            'plan_mulai'   => trim($_POST['plan_mulai'] ?? ''),
            'plan_selesai' => trim($_POST['plan_selesai'] ?? ''),
        ];

        $err = $this->validate(array_merge($d, [
            'id_jadwal'        => $id,
            'proyek_id_proyek' => $pid,
        ]), true);

        if ($err) {
            $_SESSION['form_errors']['jadwal_update'] = $err;
            $_SESSION['form_old']['jadwal_update']    = $d;
            header("Location: {$this->baseUrl}index.php?r=penjadwalan/edit&id=" . urlencode($id));
            exit;
        }

        $d['durasi'] = $this->model->diffDaysInclusive($d['plan_mulai'], $d['plan_selesai']);

        try {
            if ($this->model->update($id, $d)) {
                audit_log('jadwal.update', ['id' => $id]);
                $_SESSION['success'] = 'Jadwal berhasil diperbarui.';

                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'jadwal_updated', [
                    'proyek_id' => $pid,
                    'id_jadwal' => $id,
                    'actor_id'  => $actorId,
                ]);
            } else {
                $_SESSION['error'] = 'Tidak ada perubahan atau update gagal.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal memperbarui jadwal: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=penjadwalan&proyek=" . urlencode($pid));
        exit;
    }

    /** POST /penjadwalan/delete */
    public function delete(): void
    {
        // HANYA PM
        require_roles(['RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=penjadwalan");
            exit;
        }

        $id  = trim($_POST['hapus_id'] ?? '');
        $pid = trim($_POST['hapus_pid'] ?? '');

        try {
            if ($id && $this->model->delete($id)) {
                audit_log('jadwal.delete', ['id' => $id]);
                $_SESSION['success'] = 'Jadwal berhasil dihapus.';

                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'jadwal_deleted', [
                    'proyek_id' => $pid,
                    'id_jadwal' => $id,
                    'actor_id'  => $actorId,
                ]);
            } else {
                $_SESSION['error'] = 'Gagal menghapus jadwal.';
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal menghapus jadwal: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=penjadwalan&proyek=" . urlencode($pid));
        exit;
    }

    // ====== Validation (server-side) ======
    private function validate(array $d, bool $isUpdate = false): array
    {
        $err = [];

        // field wajib
        $req = ['id_jadwal', 'proyek_id_proyek', 'plan_mulai', 'plan_selesai'];
        if ($isUpdate) $req = ['id_jadwal', 'plan_mulai', 'plan_selesai'];
        foreach ($req as $k) if (empty($d[$k])) $err[$k] = 'Wajib diisi.';

        // ID: JDL + hanya angka
        if (!empty($d['id_jadwal'])) {
            $id = strtoupper((string)$d['id_jadwal']);
            if (!preg_match('/^JDL\d+$/', $id)) {
                $err['id_jadwal'] = 'ID harus diawali "JDL" lalu hanya angka.';
            }
        }

        // format tanggal
        $isDate = fn($x) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$x);
        if (!empty($d['plan_mulai'])   && !$isDate($d['plan_mulai']))   $err['plan_mulai']   = 'Tanggal tidak valid.';
        if (!empty($d['plan_selesai']) && !$isDate($d['plan_selesai'])) $err['plan_selesai'] = 'Tanggal tidak valid.';

        // urutan mulai ≤ selesai
        if (empty($err['plan_mulai']) && empty($err['plan_selesai']) && !empty($d['plan_mulai']) && !empty($d['plan_selesai'])) {
            if (strtotime($d['plan_selesai']) < strtotime($d['plan_mulai'])) {
                $err['plan_selesai'] = 'Plan selesai tidak boleh sebelum plan mulai.';
            }
        }

        // ===== batas terhadap tanggal proyek =====
        // cari proyek_id untuk ambil tanggal proyek (aman untuk store & update)
        $pid = (string)($d['proyek_id_proyek'] ?? '');
        if ($pid === '' && !empty($d['id_jadwal'])) {
            $row = $this->model->find($d['id_jadwal']);
            if ($row) $pid = (string)($row['proyek_id_proyek'] ?? '');
        }
        if ($pid !== '') {
            $proj = $this->model->projectDetail($pid);
            $pStart = (string)($proj['tanggal_mulai'] ?? '');
            $pEnd   = (string)($proj['tanggal_selesai'] ?? '');
            if ($isDate($pStart) && $isDate($pEnd)) {
                // plan_mulai harus di dalam [pStart, pEnd]
                if (empty($err['plan_mulai']) && !empty($d['plan_mulai'])) {
                    if (strtotime($d['plan_mulai']) < strtotime($pStart)) {
                        $err['plan_mulai'] = 'Plan mulai tidak boleh sebelum tanggal mulai proyek (' . $pStart . ').';
                    } elseif (strtotime($d['plan_mulai']) > strtotime($pEnd)) {
                        $err['plan_mulai'] = 'Plan mulai tidak boleh melewati tanggal selesai proyek (' . $pEnd . ').';
                    }
                }
                // plan_selesai harus di dalam [pStart, pEnd]
                if (empty($err['plan_selesai']) && !empty($d['plan_selesai'])) {
                    if (strtotime($d['plan_selesai']) > strtotime($pEnd)) {
                        $err['plan_selesai'] = 'Plan selesai tidak boleh melewati tanggal selesai proyek (' . $pEnd . ').';
                    } elseif (strtotime($d['plan_selesai']) < strtotime($pStart)) {
                        $err['plan_selesai'] = 'Plan selesai tidak boleh sebelum tanggal mulai proyek (' . $pStart . ').';
                    }
                }
            }
        }

        return $err;
    }
}
