<?php
session_start();
include '../config/database.php';

// ===================================
// LOGIKA TAMBAH TAHAPAN
// ===================================
if (isset($_POST['tambah_tahapan'])) {
    $id_tahapan = mysqli_real_escape_string($koneksi, $_POST['id_tahapan']);
    $nama_tahapan = mysqli_real_escape_string($koneksi, $_POST['nama_tahapan']);

    $query = "INSERT INTO daftar_tahapans (id_tahapan, nama_tahapan) VALUES ('$id_tahapan', '$nama_tahapan')";
    $hasil = mysqli_query($koneksi, $query);

    if ($hasil) {
        header('Location: ../tahapan_proyek.php?pesan=tambah_sukses');
    } else {
        header('Location: ../tahapan_proyek.php?pesan=tambah_gagal');
    }
    exit();
}

// ===================================
// LOGIKA EDIT TAHAPAN
// ===================================
if (isset($_POST['edit_tahapan'])) {
    $id_tahapan_lama = mysqli_real_escape_string($koneksi, $_POST['id_tahapan_lama']);
    $id_tahapan_baru = mysqli_real_escape_string($koneksi, $_POST['id_tahapan']);
    $nama_tahapan = mysqli_real_escape_string($koneksi, $_POST['nama_tahapan']);

    $query = "UPDATE daftar_tahapans SET id_tahapan = '$id_tahapan_baru', nama_tahapan = '$nama_tahapan' WHERE id_tahapan = '$id_tahapan_lama'";
    $hasil = mysqli_query($koneksi, $query);

    if ($hasil) {
        header('Location: ../tahapan_proyek.php?pesan=edit_sukses');
    } else {
        header('Location: ../tahapan_proyek.php?pesan=edit_gagal');
    }
    exit();
}

// ===================================
// LOGIKA HAPUS TAHAPAN
// ===================================
if (isset($_GET['hapus_id'])) {
    $id_tahapan = mysqli_real_escape_string($koneksi, $_GET['hapus_id']);

    // PENTING: Cek dulu apakah tahapan ini digunakan di jadwal_proyeks
    $cek_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_proyeks WHERE daftar_tahapans_id_tahapan = '$id_tahapan'");
    $data_cek = mysqli_fetch_assoc($cek_query);

    if ($data_cek['total'] > 0) {
        // Jika sudah digunakan, jangan hapus dan beri pesan error
        header('Location: ../tahapan_proyek.php?pesan=hapus_gagal_terpakai');
    } else {
        // Jika tidak digunakan, lanjutkan penghapusan
        $query = "DELETE FROM daftar_tahapans WHERE id_tahapan = '$id_tahapan'";
        $hasil = mysqli_query($koneksi, $query);

        if ($hasil) {
            header('Location: ../tahapan_proyek.php?pesan=hapus_sukses');
        } else {
            header('Location: ../tahapan_proyek.php?pesan=hapus_gagal');
        }
    }
    exit();
}

// Redirect jika tidak ada aksi
header('Location: ../tahapan_proyek.php');
exit();
?>