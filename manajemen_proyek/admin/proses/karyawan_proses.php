<?php
session_start();
include '../../config/database.php';

// ===================================
// LOGIKA TAMBAH KARYAWAN
// ===================================
if (isset($_POST['tambah_karyawan'])) {
    $id_karyawan = mysqli_real_escape_string($koneksi, $_POST['id_karyawan']);
    $nama_karyawan = mysqli_real_escape_string($koneksi, $_POST['nama_karyawan']);
    $no_telepon = mysqli_real_escape_string($koneksi, $_POST['no_telepon_karyawan']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];
    $id_role = mysqli_real_escape_string($koneksi, $_POST['role_id_role']);

    // Hash password untuk keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO karyawan (id_karyawan, nama_karyawan, no_telepon_karyawan, email, password, role_id_role) 
              VALUES ('$id_karyawan', '$nama_karyawan', '$no_telepon', '$email', '$hashed_password', '$id_role')";
    $hasil = mysqli_query($koneksi, $query);

    if ($hasil) {
        header('Location: ../karyawan.php?pesan=tambah_sukses');
    } else {
        header('Location: ../karyawan.php?pesan=tambah_gagal');
    }
    exit();
}

// ===================================
// LOGIKA EDIT KARYAWAN
// ===================================
if (isset($_POST['edit_karyawan'])) {
    $id_karyawan = mysqli_real_escape_string($koneksi, $_POST['id_karyawan']);
    $nama_karyawan = mysqli_real_escape_string($koneksi, $_POST['nama_karyawan']);
    $no_telepon = mysqli_real_escape_string($koneksi, $_POST['no_telepon_karyawan']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];
    $id_role = mysqli_real_escape_string($koneksi, $_POST['role_id_role']);

    // Cek apakah password diisi atau tidak
    if (!empty($password)) {
        // Jika password baru diisi, hash dan update password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE karyawan SET nama_karyawan = '$nama_karyawan', no_telepon_karyawan = '$no_telepon', email = '$email', password = '$hashed_password', role_id_role = '$id_role' WHERE id_karyawan = '$id_karyawan'";
    } else {
        // Jika password kosong, jangan update password
        $query = "UPDATE karyawan SET nama_karyawan = '$nama_karyawan', no_telepon_karyawan = '$no_telepon', email = '$email', role_id_role = '$id_role' WHERE id_karyawan = '$id_karyawan'";
    }

    $hasil = mysqli_query($koneksi, $query);

    if ($hasil) {
        header('Location: ../karyawan.php?pesan=edit_sukses');
    } else {
        header('Location: ../karyawan.php?pesan=edit_gagal');
    }
    exit();
}

// ===================================
// LOGIKA HAPUS KARYAWAN
// ===================================
if (isset($_GET['hapus_id'])) {
    $id_karyawan = mysqli_real_escape_string($koneksi, $_GET['hapus_id']);

    // Cek apakah karyawan ini terhubung dengan proyek sebagai PIC
    $cek_proyek_sales = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM proyek WHERE karyawan_id_pic_sales = '$id_karyawan'");
    $cek_proyek_site = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM proyek WHERE karyawan_id_pic_site = '$id_karyawan'");
    
    $is_used = mysqli_fetch_assoc($cek_proyek_sales)['total'] > 0 || mysqli_fetch_assoc($cek_proyek_site)['total'] > 0;

    if ($is_used) {
        // Jika karyawan terhubung dengan proyek, batalkan penghapusan
        header('Location: ../karyawan.php?pesan=hapus_gagal_terpakai');
    } else {
        // Jika tidak, lanjutkan penghapusan
        $query = "DELETE FROM karyawan WHERE id_karyawan = '$id_karyawan'";
        $hasil = mysqli_query($koneksi, $query);

        if ($hasil) {
            header('Location: ../karyawan.php?pesan=hapus_sukses');
        } else {
            header('Location: ../karyawan.php?pesan=hapus_gagal');
        }
    }
    exit();
}

header('Location: ../karyawan.php');
exit();
?>