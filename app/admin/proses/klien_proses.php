<?php
session_start();
include '../config/database.php';

// ===================================
// LOGIKA TAMBAH KLIEN
// ===================================
if (isset($_POST['tambah_klien'])) {
    $id_klien = mysqli_real_escape_string($koneksi, $_POST['id_klien']);
    $nama_klien = mysqli_real_escape_string($koneksi, $_POST['nama_klien']);
    $no_telepon_klien = mysqli_real_escape_string($koneksi, $_POST['no_telepon_klien']);
    $email_klien = mysqli_real_escape_string($koneksi, $_POST['email_klien']);
    $alamat_klien = mysqli_real_escape_string($koneksi, $_POST['alamat_klien']);

    $query = "INSERT INTO klien (id_klien, nama_klien, no_telepon_klien, email_klien, alamat_klien) 
              VALUES ('$id_klien', '$nama_klien', '$no_telepon_klien', '$email_klien', '$alamat_klien')";
    $hasil = mysqli_query($koneksi, $query);

    if ($hasil) {
        header('Location: ../klien.php?pesan=tambah_sukses');
    } else {
        header('Location: ../klien.php?pesan=tambah_gagal');
    }
    exit();
}

// ===================================
// LOGIKA EDIT KLIEN
// ===================================
if (isset($_POST['edit_klien'])) {
    $id_klien = mysqli_real_escape_string($koneksi, $_POST['id_klien']);
    $nama_klien = mysqli_real_escape_string($koneksi, $_POST['nama_klien']);
    $no_telepon_klien = mysqli_real_escape_string($koneksi, $_POST['no_telepon_klien']);
    $email_klien = mysqli_real_escape_string($koneksi, $_POST['email_klien']);
    $alamat_klien = mysqli_real_escape_string($koneksi, $_POST['alamat_klien']);

    $query = "UPDATE klien SET nama_klien = '$nama_klien', no_telepon_klien = '$no_telepon_klien', email_klien = '$email_klien', alamat_klien = '$alamat_klien' WHERE id_klien = '$id_klien'";
    $hasil = mysqli_query($koneksi, $query);

    if ($hasil) {
        header('Location: ../klien.php?pesan=edit_sukses');
    } else {
        header('Location: ../klien.php?pesan=edit_gagal');
    }
    exit();
}

// ===================================
// LOGIKA HAPUS KLIEN
// ===================================
if (isset($_GET['hapus_id'])) {
    $id_klien = mysqli_real_escape_string($koneksi, $_GET['hapus_id']);

    // Cek apakah klien ini terhubung dengan proyek
    $cek_proyek = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM proyek WHERE klien_id_klien = '$id_klien'");
    $is_used = mysqli_fetch_assoc($cek_proyek)['total'] > 0;

    if ($is_used) {
        // Jika klien terhubung dengan proyek, batalkan penghapusan
        header('Location: ../klien.php?pesan=hapus_gagal_terpakai');
    } else {
        // Jika tidak, lanjutkan penghapusan
        $query = "DELETE FROM klien WHERE id_klien = '$id_klien'";
        $hasil = mysqli_query($koneksi, $query);

        if ($hasil) {
            header('Location: ../klien.php?pesan=hapus_sukses');
        } else {
            header('Location: ../klien.php?pesan=hapus_gagal');
        }
    }
    exit();
}

header('Location: ../klien.php');
exit();
?>