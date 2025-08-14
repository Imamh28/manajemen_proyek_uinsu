<?php
include 'config/database.php';

// Menangkap data yang dikirim dari form
$email = mysqli_real_escape_string($koneksi, $_POST['email']);
$password = mysqli_real_escape_string($koneksi, $_POST['password']);

// Mencari data user di database
$query = "SELECT * FROM karyawan 
          JOIN role ON karyawan.role_id_role = role.id_role 
          WHERE karyawan.email='$email'";
$result = mysqli_query($koneksi, $query);

// Cek jumlah data yang ditemukan
$cek = mysqli_num_rows($result);

if ($cek > 0) {
    $data = mysqli_fetch_assoc($result);

    // !! CATATAN KEAMANAN PENTING !!
    // Database Anda menyimpan password dalam bentuk teks biasa ('hashed_password_1').
    // Di aplikasi nyata, gunakan password_hash() saat menyimpan dan password_verify() saat mengecek.
    // Untuk contoh ini, kita hanya akan membandingkan string secara langsung.
    if ($password == $data['password']) { // Ini TIDAK AMAN untuk produksi
        // Cek jika role adalah Admin
        if ($data['nama_role'] == 'Admin') {
            // Buat session
            $_SESSION['id_karyawan'] = $data['id_karyawan'];
            $_SESSION['nama_karyawan'] = $data['nama_karyawan'];
            $_SESSION['role'] = $data['nama_role'];
            $_SESSION['status'] = "login";
            header("location:admin/index.php");
        } else {
            // Jika bukan admin
            header("location:login.php?pesan=gagal");
        }
    } else {
        // Jika password salah
        header("location:login.php?pesan=gagal");
    }
} else {
    // Jika email tidak ditemukan
    header("location:login.php?pesan=gagal");
}
?>