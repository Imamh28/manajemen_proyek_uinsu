<?php
session_start();
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$controller = new AuthController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $controller->login($email, $password);
} else {
    header('Location: login.php');
    exit;
}
