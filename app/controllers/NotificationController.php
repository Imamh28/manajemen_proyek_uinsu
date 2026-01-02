<?php
// app/controllers/NotificationController.php
require_once __DIR__ . '/../helpers/Notify.php';

class NotificationController
{
    private string $baseUrl;

    public function __construct(private PDO $pdo, string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    private function requireLogin(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $this->baseUrl . 'auth/login.php');
            exit;
        }
    }

    private function currentUserId(): string
    {
        return (string)($_SESSION['user']['id'] ?? '');
    }

    /** RoleId robust (menangani variasi key session). */
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

    /** referer aman: hanya izinkan balik ke halaman dalam app */
    private function safeBackUrl(): string
    {
        $fallback = $this->baseUrl . 'index.php?r=dashboard';
        $back = (string)($_SERVER['HTTP_REFERER'] ?? '');
        if ($back === '') return $fallback;

        if (preg_match('#^https?://#i', $this->baseUrl)) {
            $b = parse_url($this->baseUrl);
            $u = parse_url($back);
            if (!$b || !$u) return $fallback;

            $bScheme = strtolower((string)($b['scheme'] ?? ''));
            $bHost   = strtolower((string)($b['host'] ?? ''));
            $bPath   = (string)($b['path'] ?? '/');

            $uScheme = strtolower((string)($u['scheme'] ?? ''));
            $uHost   = strtolower((string)($u['host'] ?? ''));
            $uPath   = (string)($u['path'] ?? '/');

            if ($uScheme === $bScheme && $uHost === $bHost && str_starts_with($uPath, $bPath)) {
                return $back;
            }
            return $fallback;
        }

        if (preg_match('#^https?://#i', $back)) {
            $u = parse_url($back);
            $uPath = (string)($u['path'] ?? '/');
            if (str_starts_with($uPath, $this->baseUrl)) return $back;
            return $fallback;
        }

        if (str_starts_with($back, $this->baseUrl)) return $back;
        return $fallback;
    }

    /** POST /notify/readall : hapus semua notifikasi milik user */
    public function readAll(): void
    {
        $this->requireLogin();

        $uid = $this->currentUserId();
        if ($uid !== '') {
            $st = $this->pdo->prepare("DELETE FROM notifications WHERE user_id = :u");
            $st->execute([':u' => $uid]);
        }

        header('Location: ' . $this->safeBackUrl());
        exit;
    }

    /** GET /notify/open&id=123 : hapus 1 notifikasi & redirect berdasarkan role */
    public function open(): void
    {
        $this->requireLogin();

        $uid    = $this->currentUserId();
        $roleId = $this->currentRoleId();
        $id     = (int)($_GET['id'] ?? 0);

        if ($uid === '' || $id <= 0) {
            header('Location: ' . $this->baseUrl . 'index.php?r=dashboard');
            exit;
        }

        $st = $this->pdo->prepare("SELECT * FROM notifications WHERE id=:id AND user_id=:u LIMIT 1");
        $st->execute([':id' => $id, ':u' => $uid]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            header('Location: ' . $this->baseUrl . 'index.php?r=dashboard');
            exit;
        }

        $relative = (string)notif_role_url($row, $roleId);
        if ($relative === '') $relative = 'index.php?r=dashboard';

        $target = (str_starts_with($relative, 'http') || str_starts_with($relative, $this->baseUrl))
            ? $relative
            : ($this->baseUrl . ltrim($relative, '/'));

        $del = $this->pdo->prepare("DELETE FROM notifications WHERE id=:id AND user_id=:u");
        $del->execute([':id' => $id, ':u' => $uid]);

        header('Location: ' . $target);
        exit;
    }
}
