<?php
// Ganti nilai ini sesuai dengan konfigurasi server database Anda
$host = '127.0.0.1'; // atau 'localhost'
$user = 'root';
$pass = '';
$db   = 'manajemen_proyek_db';

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Memulai session
session_start();
