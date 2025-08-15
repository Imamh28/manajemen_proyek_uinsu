<?php
$host     = 'localhost';
$dbname   = 'manajemen_proyek_db'; // typo di "pyoyek" pastikan itu benar
$username = 'root';
$password = ''; // default XAMPP tidak ada password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set error mode ke exception agar error bisa ditangkap
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
