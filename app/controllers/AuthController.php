<?php
require_once __DIR__ . '/../models/UserModel.php';

class AuthController
{
    private $userModel;
    private $baseUrl;

    public function __construct($pdo, $baseUrl)
    {
        $this->userModel = new UserModel($pdo);
        $this->baseUrl   = rtrim($baseUrl, '/') . '/';
    }

    public function login($email, $password)
    {
        // Session sudah aktif dari init.php (jangan panggil session_start di sini)
        $user = $this->userModel->findByEmail($email);

        // NOTE: pembanding password tetap plain sesuai instruksi (hardening ditunda)
        if ($user && $password === ($user['password'] ?? null)) {
            // Regenerate session ID saat login (mencegah session fixation)
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }

            // Simpan payload user minimal
            $_SESSION['user'] = [
                'id'    => $user['id']    ?? null,
                'email' => $user['email'] ?? $email,
                'name'  => $user['name']  ?? 'User',
            ];

            // Flash message sesuai alert.php (TANPA toast, sesuai template Fobia)
            $_SESSION['success'] = 'Berhasil login, selamat datang kembali!';

            header('Location: ' . $this->baseUrl . 'index.php');
            exit;
        }

        // Gagal login
        $_SESSION['error'] = 'Email atau password salah!';
        header('Location: ' . $this->baseUrl . 'auth/login.php');
        exit;
    }

    public function logout()
    {
        // Jangan session_start di sini
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        header('Location: ' . $this->baseUrl . 'auth/login.php');
        exit;
    }
}
