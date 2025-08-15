<?php
require_once __DIR__ . '/../models/UserModel.php';

class AuthController
{
    private $userModel;

    public function __construct($pdo)
    {
        $this->userModel = new UserModel($pdo);
    }

    public function login($email, $password)
    {
        $user = $this->userModel->findByEmail($email);

        if ($user && $password === $user['password']) {
            $_SESSION['user'] = $user;
            $_SESSION['success'] = "Berhasil login, selamat datang kembali!";
            header('Location: ../index.php');
            exit;
        } else {
            $_SESSION['error'] = "Email atau password salah!";
            header('Location: ../auth/login.php');
            exit;
        }
    }

    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();
        header("Location: /login.php"); // Redirect to login page
        exit;
    }
}
