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

    /** Hapus request (admin/pm) dan otomatis sync proyek. */
    public function deleteRequest(): void
    {
        require_roles(['RL001', 'RL002'], $this->baseUrl);

        if (!csrf_verify($_POST['_csrf'] ?? '')) {
            $_SESSION['error'] = 'Token tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        $id = (int)($_POST['hapus_id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'ID request tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        $st = $this->pdo->prepare("
            SELECT proyek_id_proyek, bukti_foto, bukti_dokumen
              FROM tahapan_update_requests
             WHERE id = :id
             LIMIT 1
        ");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $_SESSION['error'] = 'Data request tidak ditemukan.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        $pid  = (string)($row['proyek_id_proyek'] ?? '');
        $foto = (string)($row['bukti_foto'] ?? '');
        $doc  = (string)($row['bukti_dokumen'] ?? '');

        try {
            $this->pdo->beginTransaction();

            $del = $this->pdo->prepare("DELETE FROM tahapan_update_requests WHERE id = :id");
            $del->execute([':id' => $id]);

            if ($pid !== '') {
                $this->proyekModel->syncStateAfterRelationsChange($pid, ['Selesai', 'Dibatalkan']);
            }

            $this->pdo->commit();

            foreach ([$foto, $doc] as $rel) {
                $rel = ltrim((string)$rel, '/');
                if ($rel !== '' && str_starts_with($rel, 'uploads/tahapan/')) {
                    $abs = __DIR__ . '/../' . $rel;
                    if (is_file($abs)) @unlink($abs);
                }
            }

            $_SESSION['success'] = 'Request berhasil dihapus.';
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            $_SESSION['error'] = 'Gagal menghapus request: ' . $e->getMessage();
        }

        header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
        exit;
    }

    public function file(): void
    {
        require_roles(['RL001', 'RL002'], $this->baseUrl);

        $id = (int)($_GET['id'] ?? 0);
        $kind = trim($_GET['kind'] ?? ''); // foto | dokumen
        $download = (($_GET['download'] ?? '') === '1');

        if ($id <= 0 || !in_array($kind, ['foto', 'dokumen'], true)) {
            $_SESSION['error'] = 'Permintaan file tidak valid.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        $st = $this->pdo->prepare("SELECT bukti_foto, bukti_dokumen FROM tahapan_update_requests WHERE id = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $_SESSION['error'] = 'Data tidak ditemukan.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        $rel = $kind === 'foto' ? (string)($row['bukti_foto'] ?? '') : (string)($row['bukti_dokumen'] ?? '');
        if ($rel === '') {
            $_SESSION['error'] = 'File tidak tersedia.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        $rel = ltrim($rel, '/');

        if (!str_starts_with($rel, 'uploads/tahapan/')) {
            $_SESSION['error'] = 'Akses file ditolak.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        $abs = __DIR__ . '/../' . $rel;
        if (!is_file($abs)) {
            $_SESSION['error'] = 'File tidak ditemukan di server.';
            header("Location: {$this->baseUrl}index.php?r=tahapan-approval");
            exit;
        }

        $mime = mime_content_type($abs) ?: '';
        if ($mime === '') {
            $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
            $mime = ($ext === 'pdf') ? 'application/pdf' : 'application/octet-stream';
        }

        $filename = basename($abs);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($abs));
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . $filename . '"');

        readfile($abs);
        exit;
    }
}
