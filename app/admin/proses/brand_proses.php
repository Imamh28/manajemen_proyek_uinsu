<?php
session_start();
include '../config/database.php';

// ===================================
// LOGIKA TAMBAH BRAND
// ===================================
if (isset($_POST['tambah_brand'])) {
    $nama_brand = mysqli_real_escape_string($koneksi, $_POST['nama_brand']);

    $query = "INSERT INTO brand (nama_brand) VALUES ('$nama_brand')";
    $hasil = mysqli_query($koneksi, $query);

    if ($hasil) {
        header('Location: ../brand.php?pesan=tambah_sukses');
    } else {
        header('Location: ../brand.php?pesan=tambah_gagal');
    }
    exit();
}

// ===================================
// LOGIKA EDIT BRAND
// ===================================
if (isset($_POST['edit_brand'])) {
    $id_brand = mysqli_real_escape_string($koneksi, $_POST['id_brand']);
    $nama_brand = mysqli_real_escape_string($koneksi, $_POST['nama_brand']);

    $query = "UPDATE brand SET nama_brand = '$nama_brand' WHERE id_brand = '$id_brand'";
    $hasil = mysqli_query($koneksi, $query);

    if ($hasil) {
        header('Location: ../brand.php?pesan=edit_sukses');
    } else {
        header('Location: ../brand.php?pesan=edit_gagal');
    }
    exit();
}

// ===================================
// LOGIKA HAPUS BRAND
// ===================================
if (isset($_GET['hapus_id'])) {
    $id_brand = mysqli_real_escape_string($koneksi, $_GET['hapus_id']);

    // Cek apakah brand ini terhubung dengan proyek
    $cek_proyek = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM proyek WHERE brand_id_brand = '$id_brand'");
    $is_used = mysqli_fetch_assoc($cek_proyek)['total'] > 0;

    if ($is_used) {
        // Jika brand terhubung dengan proyek, batalkan penghapusan
        header('Location: ../brand.php?pesan=hapus_gagal_terpakai');
    } else {
        // Jika tidak, lanjutkan penghapusan
        $query = "DELETE FROM brand WHERE id_brand = '$id_brand'";
        $hasil = mysqli_query($koneksi, $query);

        if ($hasil) {
            header('Location: ../brand.php?pesan=hapus_sukses');
        } else {
            header('Location: ../brand.php?pesan=hapus_gagal');
        }
    }
    exit();
}

header('Location: ../brand.php');
exit();
?>