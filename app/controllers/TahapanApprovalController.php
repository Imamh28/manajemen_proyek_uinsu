<?php
// app/controllers/TahapanApprovalController.php

require_once __DIR__ . '/../models/TahapanApprovalModel.php';
require_once __DIR__ . '/../models/ProyekModel.php';
require_once __DIR__ . '/../utils/csrf.php';
require_once __DIR__ . '/../utils/roles.php';
require_once __DIR__ . '/../middleware/authorize.php';
require_once __DIR__ . '/../helpers/Notify.php';

class TahapanApprovalController
{
    private TahapanApprovalModel $model;
    private ProyekModel $proyekModel;

    public function __construct(private PDO $pdo, private string $baseUrl)
    {
        $this->model       = new TahapanApprovalModel($pdo);
        $this->proyekModel = new ProyekModel($pdo);
        $this->baseUrl     = rtrim($baseUrl, '/') . '/';
    }

    /** Ambil id_karyawan VALID dari session (PM). */
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

    public function index(): void
    {
        require_roles(['RL002'], $this->baseUrl);

        $BASE_URL = $this->baseUrl;
        $pending  = $this->model->pending();
        $recent   = $this->model->recent(20);

        include __DIR__ . '/../views/projek_manajer/tahapan_approval/main.php';
    }

    public function approve(): void
    {
        require_roles(['RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF tidak valid atau sesi berakhir.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        $id   = (int)($_POST['id'] ?? 0);
        $note = trim($_POST['review_note'] ?? '');

        if ($id <= 0) {
            $_SESSION['error'] = 'ID permintaan tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        $reviewerId = $this->currentKaryawanId();
        if ($reviewerId === '') {
            $_SESSION['error'] = 'Tidak bisa mendeteksi ID reviewer (id_karyawan) dari session.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        try {
            $ok = $this->model->approve($id, $reviewerId, $note);

            if (!$ok) {
                $_SESSION['error'] = 'Gagal menyetujui permintaan.';
                header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
                exit;
            }

            $st = $this->pdo->prepare("
                SELECT proyek_id_proyek, requested_tahapan_id, requested_by
                  FROM tahapan_update_requests
                 WHERE id = :id
                 LIMIT 1
            ");
            $st->execute([':id' => $id]);

            if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                $proyekId = (string)($r['proyek_id_proyek'] ?? '');
                if ($proyekId !== '') {
                    $this->proyekModel->ensureStarted($proyekId);
                }

                notif_event($this->pdo, 'tahapan_approved', [
                    'proyek_id'    => $proyekId,
                    'tahapan'      => (string)($r['requested_tahapan_id'] ?? ''),
                    'requested_by' => (string)($r['requested_by'] ?? ''),
                    'note'         => $note,
                    'actor_id'     => $reviewerId,
                ]);
            }

            $_SESSION['success'] = 'Permintaan disetujui.';
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Gagal: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
        exit;
    }

    public function reject(): void
    {
        require_roles(['RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF tidak valid atau sesi berakhir.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        $id   = (int)($_POST['id'] ?? 0);
        $note = trim($_POST['review_note'] ?? '');

        if ($id <= 0) {
            $_SESSION['error'] = 'ID permintaan tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        $reviewerId = $this->currentKaryawanId();
        if ($reviewerId === '') {
            $_SESSION['error'] = 'Tidak bisa mendeteksi ID reviewer (id_karyawan) dari session.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        try {
            $ok = $this->model->reject($id, $reviewerId, $note);

            if (!$ok) {
                $_SESSION['error'] = 'Gagal menolak permintaan.';
                header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
                exit;
            }

            $st = $this->pdo->prepare("
                SELECT proyek_id_proyek, requested_tahapan_id, requested_by
                  FROM tahapan_update_requests
                 WHERE id = :id
                 LIMIT 1
            ");
            $st->execute([':id' => $id]);

            if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                notif_event($this->pdo, 'tahapan_rejected', [
                    'proyek_id'    => (string)($r['proyek_id_proyek'] ?? ''),
                    'tahapan'      => (string)($r['requested_tahapan_id'] ?? ''),
                    'requested_by' => (string)($r['requested_by'] ?? ''),
                    'note'         => $note,
                    'actor_id'     => $reviewerId,
                ]);
            }

            $_SESSION['success'] = 'Permintaan ditolak.';
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
        exit;
    }
}
