<?php
// app/controllers/TahapanAktifController.php

require_once __DIR__ . '/../middleware/authorize.php';
require_once __DIR__ . '/../utils/csrf.php';
require_once __DIR__ . '/../models/TahapanAktifModel.php';
require_once __DIR__ . '/../models/ProyekModel.php';
require_once __DIR__ . '/../helpers/Notify.php';

class TahapanAktifController
{
    private TahapanAktifModel $m;
    private ProyekModel $proyekModel;

    private array $lockedProjectStatuses = ['Selesai', 'Dibatalkan'];

    public function __construct(private PDO $pdo, private string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
        $this->m = new TahapanAktifModel($this->pdo);
        $this->proyekModel = new ProyekModel($this->pdo);
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

    private function mandorProjectOptions(string $mandorId): array
    {
        $sql = "
            SELECT id_proyek, nama_proyek, status
              FROM proyek
             WHERE karyawan_id_pic_site = :mid
               AND status IN ('Menunggu', 'Berjalan')
             ORDER BY id_proyek DESC
        ";
        $st = $this->pdo->prepare($sql);
        $st->execute([':mid' => $mandorId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function mandorOwnsProject(string $mandorId, string $proyekId): bool
    {
        if ($mandorId === '' || $proyekId === '') return false;

        $st = $this->pdo->prepare("
            SELECT 1
              FROM proyek
             WHERE id_proyek = :pid
               AND karyawan_id_pic_site = :mid
             LIMIT 1
        ");
        $st->execute([':pid' => $proyekId, ':mid' => $mandorId]);
        return (bool)$st->fetchColumn();
    }

    public function index(): void
    {
        require_roles(['RL003'], $this->baseUrl);

        $mandorId = $this->currentKaryawanId();
        $BASE_URL = $this->baseUrl;

        if ($mandorId === '') {
            $_SESSION['error'] = 'Tidak bisa mendeteksi identitas Mandor (id_karyawan) dari session.';
            $CURRENT_PROJECT = '';
            $PROJECTS = [];
            $STEPS = [];
            $pending = [];
            $recent = [];
            $ONLY_ONE_PROJECT = false;
            $HAS_SCHEDULE = false;

            $viewPath = __DIR__ . '/../views/mandor/tahapan-aktif/main.php';
            if (!is_file($viewPath)) $viewPath = __DIR__ . '/../views/mandor/tahapan_aktif/main.php';
            include $viewPath;
            return;
        }

        $projects = $this->mandorProjectOptions($mandorId);

        if (!$projects) {
            $_SESSION['warning'] = 'Anda belum ditugaskan pada proyek aktif (Menunggu/Berjalan).';
            $CURRENT_PROJECT = '';
            $PROJECTS = [];
            $STEPS = [];
            $pending = [];
            $recent = [];
            $ONLY_ONE_PROJECT = false;
            $HAS_SCHEDULE = false;

            $viewPath = __DIR__ . '/../views/mandor/tahapan-aktif/main.php';
            if (!is_file($viewPath)) $viewPath = __DIR__ . '/../views/mandor/tahapan_aktif/main.php';
            include $viewPath;
            return;
        }

        $ONLY_ONE_PROJECT = (count($projects) === 1);

        $allowedIds = array_map(fn($r) => (string)$r['id_proyek'], $projects);

        $projectId = trim($_GET['proyek'] ?? '');
        if ($projectId === '' || !in_array($projectId, $allowedIds, true)) {
            $projectId = (string)$projects[0]['id_proyek'];
        }

        $HAS_SCHEDULE = ($projectId !== '') ? $this->m->hasSchedule($projectId) : false;

        if ($projectId && !$HAS_SCHEDULE) {
            $_SESSION['warning'] = 'Proyek belum memiliki penjadwalan. Tunggu Project Manager membuat penjadwalan terlebih dahulu.';
            $rows = [];
            $pending = [];
            $recent = [];
        } else {
            $rows = $projectId ? $this->m->stepsByProject($projectId) : [];
            $pending = $projectId ? $this->m->pendingForUser($mandorId, $projectId) : [];
            $recent  = $projectId ? $this->m->recentForUser($mandorId, 10, $projectId) : [];
        }

        $CURRENT_PROJECT = $projectId;
        $STEPS           = $rows;
        $PROJECTS        = $projects;

        $viewPath = __DIR__ . '/../views/mandor/tahapan-aktif/main.php';
        if (!is_file($viewPath)) $viewPath = __DIR__ . '/../views/mandor/tahapan_aktif/main.php';
        include $viewPath;
    }

    public function update(): void
    {
        require_roles(['RL003'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header('Location: ' . $this->baseUrl . 'index.php?r=tahapan-aktif');
            exit;
        }

        $mandorId = $this->currentKaryawanId();
        if ($mandorId === '') {
            $_SESSION['error'] = 'Tidak bisa mendeteksi identitas Mandor (id_karyawan) dari session.';
            header('Location: ' . $this->baseUrl . 'index.php?r=tahapan-aktif');
            exit;
        }

        $proyekId = trim($_POST['proyek_id_proyek'] ?? '');
        $idTahap  = trim($_POST['id_tahapan'] ?? '');
        $note     = trim($_POST['catatan'] ?? '');

        if ($proyekId === '' || $idTahap === '') {
            $_SESSION['error'] = 'Data tidak lengkap.';
            header('Location: ' . $this->baseUrl . 'index.php?r=tahapan-aktif');
            exit;
        }

        if (!$this->mandorOwnsProject($mandorId, $proyekId)) {
            $_SESSION['error'] = 'Akses ditolak. Proyek bukan tanggung jawab Anda.';
            header('Location: ' . $this->baseUrl . 'index.php?r=tahapan-aktif');
            exit;
        }

        $proj = $this->proyekModel->find($proyekId);
        if ($proj) {
            $st = (string)($proj['status'] ?? '');
            if (in_array($st, $this->lockedProjectStatuses, true)) {
                $_SESSION['error'] = 'Tidak bisa mengajukan tahapan: proyek berstatus "' . $st . '".';
                header('Location: ' . $this->baseUrl . 'index.php?r=tahapan-aktif');
                exit;
            }
        }

        if (!$this->m->hasSchedule($proyekId)) {
            $_SESSION['error'] = 'Tidak bisa mengajukan tahapan: proyek belum memiliki penjadwalan.';
            header('Location: ' . $this->baseUrl . 'index.php?r=tahapan-aktif');
            exit;
        }

        $steps = $this->m->stepsByProject($proyekId);

        $allowed = null;
        foreach ($steps as $s) {
            if (!empty($s['_eligible'])) {
                $allowed = (string)$s['id_tahapan'];
                break;
            }
        }

        if (!$allowed || $allowed !== $idTahap) {
            $_SESSION['error'] = 'Tahapan yang diajukan tidak valid / belum saatnya (atau masih ada pengajuan pending).';
            header('Location: ' . $this->baseUrl . 'index.php?r=tahapan-aktif');
            exit;
        }

        try {
            $ok = $this->m->createRequest($proyekId, $idTahap, $mandorId, $note);

            if ($ok) {
                $this->proyekModel->ensureStarted($proyekId);

                $by = $_SESSION['user']['nama_karyawan'] ?? 'Mandor';
                notif_event($this->pdo, 'tahapan_request', [
                    'proyek_id' => $proyekId,
                    'tahapan'   => $idTahap,
                    'who'       => $by,
                    'actor_id'  => $mandorId,
                ]);
            }

            $_SESSION['success'] = $ok
                ? 'Pengajuan dikirim. Menunggu persetujuan Project Manager.'
                : 'Gagal mengirim pengajuan (mungkin masih ada pending sebelumnya).';
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal mengirim pengajuan: ' . $e->getMessage();
        }

        header('Location: ' . $this->baseUrl . 'index.php?r=tahapan-aktif');
        exit;
    }
}
