<?php
// Jangan panggil session_start(); sudah di-handle oleh init.php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$controller = new AuthController($pdo, $BASE_URL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $controller->login($email, $password);
} else {
    header('Location: ' . $BASE_URL . 'auth/login.php');
    exit;
}
