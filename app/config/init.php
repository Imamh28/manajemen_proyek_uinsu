<?php
// Session auto-expire saat browser ditutup (hapus saat browser tutup)
session_set_cookie_params([
    'lifetime' => 0, // 0 = sampai browser ditutup
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Expire manual dalam 10 menit jika tidak aktif
if (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > 600) {
    session_unset();
    session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();

$params = require __DIR__ . '/params.php';

$BASE_URL = $params['BASE_URL'];
$APP_NAME = $params['APP_NAME'];

require_once __DIR__ . '/database.php';
