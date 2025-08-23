<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$auth = new AuthController($pdo, $BASE_URL);
$auth->logout();
