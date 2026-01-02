<?php
// app/controllers/PenjadwalanController.php
declare(strict_types=1);

require_once __DIR__ . '/../models/PenjadwalanModel.php';
require_once __DIR__ . '/../helpers/Audit.php';
require_once __DIR__ . '/../utils/csrf.php';
require_once __DIR__ . '/../utils/roles.php';
require_once __DIR__ . '/../middleware/authorize.php';
require_once __DIR__ . '/../helpers/Notify.php';

class PenjadwalanController
{
    private PenjadwalanModel $model;

    private array $lockedProjectStatuses = ['Selesai', 'Dibatalkan'];

    public function __construct(private PDO $pdo, private string $baseUrl)
    {
        $this->model   = new PenjadwalanModel($pdo);
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
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

    private function ensureProjectStarted(string $proyekId): void
    {
        $st = $this->pdo->prepare("UPDATE proyek SET status='Berjalan' WHERE id_proyek=:id AND status='Menunggu'");
        $st->execute([':id' => $proyekId]);
    }

    private function assertProjectSchedulable(string $proyekId): ?string
    {
        if ($proyekId === '') return 'Proyek wajib dipilih.';
        $proj = $this->model->projectDetail($proyekId);
        if (!$proj) return 'Proyek tidak ditemukan.';

        $st = (string)($proj['status'] ?? '');
        if (in_array($st, $this->lockedProjectStatuses, true)) {
            return 'Proyek berstatus "' . $st . '" sehingga jadwal tidak dapat ditambah/diubah.';
        }
        return null;
    }

    public function index(): void
    {
        require_roles(['RL002'], $this->baseUrl);

        $proyekId = trim($_GET['proyek'] ?? '');
        $projects = $this->model->projects();

        if ($proyekId === '' && !empty($projects)) {
            $proyekId = (string)$projects[0]['id_proyek'];
        }

        $project = $proyekId ? $this->model->projectDetail($proyekId) : null;
        $rows    = $proyekId ? $this->model->all($proyekId) : [];

        $NEXT_TAHAP = $proyekId ? ($this->model->nextTahapanId($proyekId) ?? '-') : '-';
        $EXISTING_IDS_JSON = json_encode($this->model->existingIdsByProject($proyekId), JSON_UNESCAPED_UNICODE);

        $HAS_PAYMENT = $proyekId ? $this->model->hasAnyPayment($proyekId) : false;
        if ($proyekId && !$HAS_PAYMENT) {
            $_SESSION['warning'] = 'Proyek ini belum memiliki pembayaran. Sesuai flow, lakukan pembayaran terlebih dahulu sebelum membuat penjadwalan.';
        }

        $BASE_URL   = $this->baseUrl;
        $projek     = $project;
        $jadwal     = $rows;
        $projectsDD = $projects;

        include __DIR__ . '/../views/projek_manajer/jadwal/main.php';
    }

    public function editForm(): void
    {
        require_roles(['RL002'], $this->baseUrl);

        $id  = trim($_GET['id'] ?? '');
        if ($id === '') {
            $_SESSION['error'] = 'ID jadwal tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=penjadwalan");
            exit;
        }

        $row = $this->model->find($id);
        if (!$row) {
            $_SESSION['error'] = 'Data jadwal tidak ditemukan.';
            header("Location: {$this->baseUrl}index.php?r=penjadwalan");
            exit;
        }

        $pid = (string)($row['proyek_id_proyek'] ?? '');
        $project = $pid ? $this->model->projectDetail($pid) : null;

        $BASE_URL = $this->baseUrl;
        $jadwal   = $row;
        $projek   = $project;

        include __DIR__ . '/../views/projek_manajer/jadwal/edit.php';
    }

    public function detailProyek(): void
    {
        require_roles(['RL002'], $this->baseUrl);

        $pid    = trim($_GET['proyek'] ?? '');
        $projek = $pid ? $this->model->projectDetail($pid) : null;

        $BASE_URL = $this->baseUrl;
        include __DIR__ . '/../views/projek_manajer/detailproyek/main.php';
    }

    public function detailPembayaran(): void
    {
        require_roles(['RL002'], $this->baseUrl);

        $pid        = trim($_GET['proyek'] ?? '');
        $projek     = $pid ? $this->model->projectDetail($pid) : null;
        $pembayaran = $pid ? $this->model->paymentsByProject($pid) : [];

        $BASE_URL = $this->baseUrl;
        include __DIR__ . '/../views/projek_manajer/detailpembayaran/main.php';
    }

    public function store(): void
    {
        require_roles(['RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=penjadwalan");
            exit;
        }

        $d = [
            'id_jadwal'        => strtoupper(trim($_POST['id_jadwal'] ?? '')),
            'proyek_id_proyek' => strtoupper(trim($_POST['proyek_id_proyek'] ?? '')),
            'plan_mulai'       => trim($_POST['plan_mulai'] ?? ''),
            'plan_selesai'     => trim($_POST['plan_selesai'] ?? ''),
        ];

        if (!$this->model->hasAnyPayment($d['proyek_id_proyek'])) {
            $_SESSION['error'] = 'Tidak bisa membuat penjadwalan: proyek belum memiliki pembayaran. Silakan lakukan pembayaran terlebih dahulu.';
            header("Location: {$this->baseUrl}index.php?r=penjadwalan&proyek=" . urlencode($d['proyek_id_proyek']));
            exit;
        }

        $err = $this->validate($d, false, null);

        if (empty($err['proyek_id_proyek'])) {
            $lockErr = $this->assertProjectSchedulable($d['proyek_id_proyek']);
            if ($lockErr) $err['proyek_id_proyek'] = $lockErr;
        }

        if (empty($err['id_jadwal']) && $this->model->existsId($d['id_jadwal'])) {
            $err['id_jadwal'] = 'ID Jadwal sudah dipakai.';
        }

        $autoTahap = null;
        if (empty($err['proyek_id_proyek'])) {
            $autoTahap = $this->model->nextTahapanId($d['proyek_id_proyek']);
            if (!$autoTahap) {
                $err['id_jadwal'] = 'Semua tahapan untuk proyek ini sudah dibuat.';
            }
        }

        if ($err) {
            $_SESSION['form_errors']['jadwal_store'] = $err;
            $_SESSION['form_old']['jadwal_store']    = $d;
            header("Location: {$this->baseUrl}index.php?r=penjadwalan&proyek=" . urlencode($d['proyek_id_proyek']));
            exit;
        }

        $d['durasi']                    = $this->model->diffDaysInclusive($d['plan_mulai'], $d['plan_selesai']);
        $d['daftar_tahapans_id_tahapan'] = $autoTahap;

        try {
            $this->pdo->beginTransaction();

            $ok = $this->model->create($d);
            if ($ok) {
                $this->ensureProjectStarted($d['proyek_id_proyek']);

                // ✅ set current_tahapan_id ke tahapan yang baru dibuat jadwalnya
                $st = $this->pdo->prepare("UPDATE proyek SET current_tahapan_id = :t WHERE id_proyek = :p");
                $st->execute([':t' => $autoTahap, ':p' => $d['proyek_id_proyek']]);

                // ✅ recalc status jadwal
                $this->model->recalcStatusForProject($d['proyek_id_proyek']);

                $this->pdo->commit();

                audit_log('jadwal.store', ['id' => $d['id_jadwal'], 'proyek' => $d['proyek_id_proyek']]);
                $_SESSION['success'] = 'Jadwal berhasil ditambahkan (tahapan otomatis: ' . $autoTahap . ').';

                $actorKid = $this->currentKaryawanId();
                notif_event($this->pdo, 'jadwal_created', [
                    'proyek_id' => $d['proyek_id_proyek'],
                    'id_jadwal' => $d['id_jadwal'],
                    'actor_id'  => $actorKid,
                ]);
            } else {
                $this->pdo->rollBack();
                $_SESSION['error'] = 'Gagal menambah jadwal.';
            }
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            $_SESSION['error'] = 'Gagal menambah jadwal: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=penjadwalan&proyek=" . urlencode($d['proyek_id_proyek']));
        exit;
    }

    public function update(): void
    {
        require_roles(['RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=penjadwalan");
            exit;
        }

        $id = strtoupper(trim($_POST['id_jadwal'] ?? ''));
        if ($id === '') {
            $_SESSION['error'] = 'ID jadwal tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=penjadwalan");
            exit;
        }

        $row = $this->model->find($id);
        if (!$row) {
            $_SESSION['error'] = 'Data jadwal tidak ditemukan.';
            header("Location: {$this->baseUrl}index.php?r=penjadwalan");
            exit;
        }
        $pid = (string)($row['proyek_id_proyek'] ?? '');

        $lockErr = $this->assertProjectSchedulable($pid);
        if ($lockErr) {
            $_SESSION['error'] = $lockErr;
            header("Location: {$this->baseUrl}index.php?r=penjadwalan&proyek=" . urlencode($pid));
            exit;
        }

        $d = [
            'plan_mulai'   => trim($_POST['plan_mulai'] ?? ''),
            'plan_selesai' => trim($_POST['plan_selesai'] ?? ''),
        ];

        $err = $this->validate([
            'id_jadwal'        => $id,
            'proyek_id_proyek' => $pid,
            'plan_mulai'       => $d['plan_mulai'],
            'plan_selesai'     => $d['plan_selesai'],
        ], true, $id);

        if ($err) {
            $_SESSION['form_errors']['jadwal_update'] = $err;
            $_SESSION['form_old']['jadwal_update']    = $d;
            header("Location: {$this->baseUrl}index.php?r=penjadwalan/edit&id=" . urlencode($id));
            exit;
        }

        $d['durasi'] = $this->model->diffDaysInclusive($d['plan_mulai'], $d['plan_selesai']);

        try {
            if ($this->model->update($id, $d)) {
                // ✅ recalc status jadwal
                $this->model->recalcStatusForProject($pid);

                audit_log('jadwal.update', ['id' => $id]);
                $_SESSION['success'] = 'Jadwal berhasil diperbarui.';

                $actorKid = $this->currentKaryawanId();
                notif_event($this->pdo, 'jadwal_updated', [
                    'proyek_id' => $pid,
                    'id_jadwal' => $id,
                    'actor_id'  => $actorKid,
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

    public function delete(): void
    {
        require_roles(['RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=penjadwalan");
            exit;
        }

        $id = strtoupper(trim($_POST['hapus_id'] ?? ''));
        if ($id === '') {
            $_SESSION['error'] = 'ID jadwal tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=penjadwalan");
            exit;
        }

        $row = $this->model->find($id);
        if (!$row) {
            $_SESSION['error'] = 'Data jadwal tidak ditemukan.';
            header("Location: {$this->baseUrl}index.php?r=penjadwalan");
            exit;
        }
        $pid = (string)($row['proyek_id_proyek'] ?? '');

        $lockErr = $this->assertProjectSchedulable($pid);
        if ($lockErr) {
            $_SESSION['error'] = $lockErr;
            header("Location: {$this->baseUrl}index.php?r=penjadwalan&proyek=" . urlencode($pid));
            exit;
        }

        try {
            if ($this->model->delete($id)) {
                $this->model->recalcStatusForProject($pid);

                audit_log('jadwal.delete', ['id' => $id]);
                $_SESSION['success'] = 'Jadwal berhasil dihapus.';

                $actorKid = $this->currentKaryawanId();
                notif_event($this->pdo, 'jadwal_deleted', [
                    'proyek_id' => $pid,
                    'id_jadwal' => $id,
                    'actor_id'  => $actorKid,
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

    private function validate(array $d, bool $isUpdate = false, ?string $excludeId = null): array
    {
        $err = [];

        $req = $isUpdate
            ? ['id_jadwal', 'plan_mulai', 'plan_selesai']
            : ['id_jadwal', 'proyek_id_proyek', 'plan_mulai', 'plan_selesai'];

        foreach ($req as $k) {
            if (empty($d[$k])) $err[$k] = 'Wajib diisi.';
        }

        if (!empty($d['id_jadwal'])) {
            $id = strtoupper((string)$d['id_jadwal']);
            if (!preg_match('/^JDL\d+$/', $id)) {
                $err['id_jadwal'] = 'ID harus diawali "JDL" lalu hanya angka.';
            }
        }

        $isDate = fn($x) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$x);
        if (!empty($d['plan_mulai'])   && !$isDate($d['plan_mulai']))   $err['plan_mulai']   = 'Tanggal tidak valid.';
        if (!empty($d['plan_selesai']) && !$isDate($d['plan_selesai'])) $err['plan_selesai'] = 'Tanggal tidak valid.';

        if (empty($err['plan_mulai']) && empty($err['plan_selesai']) && !empty($d['plan_mulai']) && !empty($d['plan_selesai'])) {
            if (strtotime($d['plan_selesai']) < strtotime($d['plan_mulai'])) {
                $err['plan_selesai'] = 'Plan selesai tidak boleh sebelum plan mulai.';
            }
        }

        $pid = (string)($d['proyek_id_proyek'] ?? '');
        if ($pid === '' && !empty($d['id_jadwal'])) {
            $row = $this->model->find((string)$d['id_jadwal']);
            if ($row) $pid = (string)($row['proyek_id_proyek'] ?? '');
        }

        // batas tanggal proyek
        if ($pid !== '') {
            $proj = $this->model->projectDetail($pid);
            $pStart = (string)($proj['tanggal_mulai'] ?? '');
            $pEnd   = (string)($proj['tanggal_selesai'] ?? '');

            if ($isDate($pStart) && $isDate($pEnd)) {
                if (empty($err['plan_mulai']) && !empty($d['plan_mulai'])) {
                    if (strtotime($d['plan_mulai']) < strtotime($pStart)) {
                        $err['plan_mulai'] = 'Plan mulai tidak boleh sebelum tanggal mulai proyek (' . $pStart . ').';
                    } elseif (strtotime($d['plan_mulai']) > strtotime($pEnd)) {
                        $err['plan_mulai'] = 'Plan mulai tidak boleh melewati tanggal selesai proyek (' . $pEnd . ').';
                    }
                }

                if (empty($err['plan_selesai']) && !empty($d['plan_selesai'])) {
                    if (strtotime($d['plan_selesai']) > strtotime($pEnd)) {
                        $err['plan_selesai'] = 'Plan selesai tidak boleh melewati tanggal selesai proyek (' . $pEnd . ').';
                    } elseif (strtotime($d['plan_selesai']) < strtotime($pStart)) {
                        $err['plan_selesai'] = 'Plan selesai tidak boleh sebelum tanggal mulai proyek (' . $pStart . ').';
                    }
                }
            }
        }

        // ✅ aturan linear & tidak overlap
        if ($pid !== '' && empty($err['plan_mulai']) && empty($err['plan_selesai']) && !empty($d['plan_mulai']) && !empty($d['plan_selesai'])) {

            // store: tidak boleh buat jadwal baru kalau masih ada yang belum selesai
            if (!$isUpdate) {
                if ($this->model->hasUnfinishedSchedule($pid)) {
                    $err['proyek_id_proyek'] = 'Tidak bisa menambah jadwal tahapan baru: masih ada tahapan yang belum selesai (menunggu approval/selesai).';
                } else {
                    $last = $this->model->lastScheduleByTahapan($pid);
                    if ($last && !empty($last['plan_selesai'])) {
                        // aturan non-overlap yang tegas: mulai > plan_selesai sebelumnya
                        if (strtotime($d['plan_mulai']) <= strtotime((string)$last['plan_selesai'])) {
                            $err['plan_mulai'] = 'Plan mulai harus setelah plan selesai jadwal sebelumnya (' . $last['daftar_tahapans_id_tahapan'] . ' selesai plan: ' . $last['plan_selesai'] . ').';
                        }
                    }
                }
            }

            // update: pastikan tidak overlap dengan jadwal lain
            if ($this->model->hasOverlap($pid, $d['plan_mulai'], $d['plan_selesai'], $excludeId)) {
                $err['plan_mulai'] = $err['plan_mulai'] ?? 'Rentang plan overlap dengan jadwal lain di proyek ini.';
                $err['plan_selesai'] = $err['plan_selesai'] ?? 'Rentang plan overlap dengan jadwal lain di proyek ini.';
            }

            // update: jaga urutan berdasarkan tahapan (THxx)
            if ($isUpdate && $excludeId) {
                $cur = $this->model->find($excludeId);
                $tahap = (string)($cur['daftar_tahapans_id_tahapan'] ?? '');
                if ($tahap !== '') {
                    $prev = $this->model->prevScheduleByTahapan($pid, $tahap);
                    if ($prev && !empty($prev['plan_selesai'])) {
                        if (strtotime($d['plan_mulai']) <= strtotime((string)$prev['plan_selesai'])) {
                            $err['plan_mulai'] = 'Plan mulai harus setelah plan selesai tahapan sebelumnya (' . $prev['daftar_tahapans_id_tahapan'] . ': ' . $prev['plan_selesai'] . ').';
                        }
                    }
                    $next = $this->model->nextScheduleByTahapan($pid, $tahap);
                    if ($next && !empty($next['plan_mulai'])) {
                        if (strtotime($d['plan_selesai']) >= strtotime((string)$next['plan_mulai'])) {
                            $err['plan_selesai'] = 'Plan selesai harus sebelum plan mulai tahapan berikutnya (' . $next['daftar_tahapans_id_tahapan'] . ': ' . $next['plan_mulai'] . ').';
                        }
                    }
                }
            }
        }

        return $err;
    }
}
