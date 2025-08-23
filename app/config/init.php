<?php
// Set nama session khusus (opsional, untuk isolasi)
if (session_status() === PHP_SESSION_NONE) {
    // Tentukan apakah HTTPS
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    session_set_cookie_params([
        'lifetime' => 0,        // sampai browser ditutup
        'path'     => '/',
        'domain'   => '',       // biarkan default
        'secure'   => $isHttps, // kirim cookie hanya via HTTPS jika tersedia
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // Optional: session_name('MPXSESSID'); // aktifkan jika ingin custom name
    session_start();
}

// Idle timeout 10 menit
if (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > 600) {
    session_unset();
    session_destroy();
    session_start(); // mulai ulang agar halaman berikutnya punya sesi bersih
}
$_SESSION['LAST_ACTIVITY'] = time();

// Regenerasi berkala setiap 5 menit untuk keamanan
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} elseif (time() - $_SESSION['CREATED'] > 300) {
    if (session_status() === PHP_SESSION_ACTIVE) session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}

$params = require __DIR__ . '/params.php';

$BASE_URL = $params['BASE_URL'];
$APP_NAME = $params['APP_NAME'];

require_once __DIR__ . '/database.php';
