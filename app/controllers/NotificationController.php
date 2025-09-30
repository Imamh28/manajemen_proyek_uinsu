<?php
// app/controllers/NotificationController.php
require_once __DIR__ . '/../helpers/Notify.php';

class NotificationController
{
    public function __construct(private PDO $pdo, private string $baseUrl) {}

    /** POST /notify/readall : hapus semua notifikasi milik user */
    public function readAll(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $this->baseUrl . 'auth/login.php');
            exit;
        }
        $uid = (string)($_SESSION['user']['id'] ?? '');
        if ($uid !== '') {
            // HAPUS semua notifikasi user ini (read/unread)
            $st = $this->pdo->prepare("DELETE FROM notifications WHERE user_id = :u");
            $st->execute([':u' => $uid]);
        }
        // balik ke halaman sebelumnya kalau ada
        $back = $_SERVER['HTTP_REFERER'] ?? ($this->baseUrl . 'index.php?r=dashboard');
        header('Location: ' . $back);
        exit;
    }

    /** GET /notify/open&id=123 : hapus 1 notifikasi & redirect berdasarkan role */
    public function open(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $this->baseUrl . 'auth/login.php');
            exit;
        }
        $uid    = (string)($_SESSION['user']['id'] ?? '');
        $roleId = (string)($_SESSION['user']['role_id'] ?? '');
        $id     = (int)($_GET['id'] ?? 0);

        if ($uid === '' || $id <= 0) {
            header('Location: ' . $this->baseUrl . 'index.php?r=dashboard');
            exit;
        }

        // ambil notifikasi milik user
        $st = $this->pdo->prepare("SELECT * FROM notifications WHERE id=:id AND user_id=:u LIMIT 1");
        $st->execute([':id' => $id, ':u' => $uid]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            header('Location: ' . $this->baseUrl . 'index.php?r=dashboard');
            exit;
        }

        // tentukan URL aman sesuai role (sebelum dihapus)
        $relative = notif_role_url($row, $roleId);
        $target   = (str_starts_with($relative, 'http') || str_starts_with($relative, $this->baseUrl))
            ? $relative
            : ($this->baseUrl . $relative);

        // HAPUS notifikasi yang dibuka
        $del = $this->pdo->prepare("DELETE FROM notifications WHERE id=:id AND user_id=:u");
        $del->execute([':id' => $id, ':u' => $uid]);

        header('Location: ' . $target);
        exit;
    }
}
