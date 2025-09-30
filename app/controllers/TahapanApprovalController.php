<?php
// app/controllers/TahapanApprovalController.php
require_once __DIR__ . '/../models/TahapanApprovalModel.php';
require_once __DIR__ . '/../utils/csrf.php';
require_once __DIR__ . '/../middleware/authorize.php';

class TahapanApprovalController
{
    private TahapanApprovalModel $model;

    public function __construct(private PDO $pdo, private string $baseUrl)
    {
        $this->model = new TahapanApprovalModel($pdo);
    }

    /** GET /tahapan-approval */
    public function index(): void
    {
        require_roles(['RL002'], $this->baseUrl);
        $BASE_URL = $this->baseUrl;
        $pending  = $this->model->pending();
        $recent   = $this->model->recent(20);

        include __DIR__ . '/../views/projek_manajer/tahapan_approval/main.php';
    }

    /** POST /tahapan-approval/approve */
    public function approve(): void
    {
        require_roles(['RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        $id   = (int)($_POST['id'] ?? 0);
        $note = trim($_POST['review_note'] ?? '');

        try {
            $ok = $id ? $this->model->approve($id, (string)$_SESSION['user']['id'], $note) : false;
            if ($ok) {
                require_once __DIR__ . '/../helpers/Notify.php';
                $st = $this->pdo->prepare("SELECT proyek_id_proyek, requested_tahapan_id, requested_by FROM tahapan_update_requests WHERE id=:id");
                $st->execute([':id' => $id]);
                if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                    $actorId = (string)($_SESSION['user']['id'] ?? '');
                    notif_event($this->pdo, 'tahapan_approved', [
                        'proyek_id'    => (string)$r['proyek_id_proyek'],
                        'tahapan'      => (string)$r['requested_tahapan_id'],
                        'requested_by' => (string)$r['requested_by'],
                        'note'         => $note,
                        'actor_id'     => $actorId,
                    ]);
                }
            }
            $_SESSION['success'] = $ok ? 'Permintaan disetujui.' : 'Gagal menyetujui.';
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
        exit;
    }

    /** POST /tahapan-approval/reject */
    public function reject(): void
    {
        require_roles(['RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        $id   = (int)($_POST['id'] ?? 0);
        $note = trim($_POST['review_note'] ?? '');

        try {
            $ok = $id ? $this->model->reject($id, (string)$_SESSION['user']['id'], $note) : false;
            if ($ok) {
                require_once __DIR__ . '/../helpers/Notify.php';
                $st = $this->pdo->prepare("SELECT proyek_id_proyek, requested_tahapan_id, requested_by FROM tahapan_update_requests WHERE id=:id");
                $st->execute([':id' => $id]);
                if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                    $actorId = (string)($_SESSION['user']['id'] ?? '');
                    notif_event($this->pdo, 'tahapan_rejected', [
                        'proyek_id'    => (string)$r['proyek_id_proyek'],
                        'tahapan'      => (string)$r['requested_tahapan_id'],
                        'requested_by' => (string)$r['requested_by'],
                        'note'         => $note,
                        'actor_id'     => $actorId,
                    ]);
                }
            }
            $_SESSION['success'] = $ok ? 'Permintaan ditolak.' : 'Gagal menolak permintaan.';
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
        exit;
    }
}
