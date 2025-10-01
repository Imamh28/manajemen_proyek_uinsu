<?php
// app/controllers/TahapanAktifController.php
require_once __DIR__ . '/../middleware/authorize.php';
require_once __DIR__ . '/../utils/csrf.php';
require_once __DIR__ . '/../models/TahapanAktifModel.php';

class TahapanAktifController
{
    public function __construct(private PDO $pdo, private string $baseUrl) {}

    /** GET /tahapan-aktif/index pengajuan tahapan */
    public function index(): void
    {
        require_roles(['RL003'], $this->baseUrl);

        $m = new TahapanAktifModel($this->pdo);

        $userId    = (string)($_SESSION['user']['id'] ?? '');
        $projectId = trim($_GET['proyek'] ?? '');
        $projects  = $m->projectOptions();

        // Jika belum dipilih, default ke proyek dari pengajuan tahapan terakhir user.
        if ($projectId === '') {
            $projectId = $m->lastRequestedProjectForUser($userId)
                ?? ($projects[0]['id_proyek'] ?? ''); // fallback: proyek pertama jika belum pernah mengajukan
        }

        $rows   = $projectId ? $m->stepsByProject($projectId) : [];

        // pending & riwayat DIBATASI pada proyek yang sedang tampil
        $pending = $projectId ? $m->pendingForUser($userId, $projectId) : [];
        $recent  = $projectId ? $m->recentForUser($userId, 10, $projectId) : [];

        $BASE_URL        = rtrim($this->baseUrl, '/') . '/';
        $CURRENT_PROJECT = $projectId;
        $STEPS           = $rows;
        $PROJECTS        = $projects;

        $viewPath = __DIR__ . '/../views/mandor/tahapan-aktif/main.php';
        if (!is_file($viewPath)) $viewPath = __DIR__ . '/../views/mandor/tahapan_aktif/main.php';
        include $viewPath;
    }

    /** POST /tahapan-aktif/update */
    public function update(): void
    {
        require_roles(['RL003'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Sesi berakhir atau token tidak valid.';
            header('Location: ' . $this->baseUrl . 'index.php?r=tahapan-aktif');
            exit;
        }

        $proyekId = trim($_POST['proyek_id_proyek'] ?? '');
        $idTahap  = trim($_POST['id_tahapan'] ?? '');
        $note     = trim($_POST['catatan'] ?? '');

        if ($proyekId === '' || $idTahap === '') {
            $_SESSION['error'] = 'Data tidak lengkap.';
            header('Location: ' . $this->baseUrl . 'index.php?r=tahapan-aktif&proyek=' . urlencode($proyekId));
            exit;
        }

        $m = new TahapanAktifModel($this->pdo);
        // validasi: hanya tahap _eligible yang boleh diajukan
        $steps = $m->stepsByProject($proyekId);
        $allowed = null;
        foreach ($steps as $s) if (!empty($s['_eligible'])) {
            $allowed = $s['id_tahapan'];
            break;
        }
        if (!$allowed || $allowed !== $idTahap) {
            $_SESSION['error'] = 'Tahapan yang diajukan tidak valid / belum saatnya.';
            header('Location: ' . $this->baseUrl . 'index.php?r=tahapan-aktif&proyek=' . urlencode($proyekId));
            exit;
        }

        try {
            $ok = $m->createRequest($proyekId, $idTahap, (string)$_SESSION['user']['id'], $note);
            require_once __DIR__ . '/../helpers/Notify.php';
            if ($ok) {
                $by = $_SESSION['user']['nama_karyawan'] ?? 'Mandor';
                $actorId = (string)($_SESSION['user']['id'] ?? '');
                notif_event($this->pdo, 'tahapan_request', [
                    'proyek_id' => $proyekId,
                    'tahapan'   => $idTahap,
                    'who'       => $by,
                    'actor_id'  => $actorId,
                ]);
            }
            $_SESSION['success'] = $ok
                ? 'Pengajuan dikirim. Menunggu persetujuan Project Manager.'
                : 'Gagal mengirim pengajuan.';
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal mengirim pengajuan: ' . $e->getMessage();
        }

        header('Location: ' . $this->baseUrl . 'index.php?r=tahapan-aktif&proyek=' . urlencode($proyekId));
        exit;
    }
}
