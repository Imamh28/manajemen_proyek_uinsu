<?php
// Mulai sesi untuk kemungkinan penggunaan pesan notifikasi di masa depan
session_start();

// 1. Panggil file konfigurasi database
include '../config/database.php';

//======================================================================
// LOGIKA UNTUK MENAMBAH PROYEK BARU
//======================================================================
if (isset($_POST['tambah_proyek'])) {

    // Ambil semua data dari form dan lakukan escaping dasar untuk keamanan
    $id_proyek = mysqli_real_escape_string($koneksi, $_POST['id_proyek']);
    $nama_proyek = mysqli_real_escape_string($koneksi, $_POST['nama_proyek']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $total_biaya_proyek = mysqli_real_escape_string($koneksi, $_POST['total_biaya_proyek']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $tanggal_mulai = $_POST['tanggal_mulai']; // Tanggal bisa kosong
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $klien_id_klien = mysqli_real_escape_string($koneksi, $_POST['klien_id_klien']);
    $brand_id_brand = mysqli_real_escape_string($koneksi, $_POST['brand_id_brand']);
    $karyawan_id_pic_sales = mysqli_real_escape_string($koneksi, $_POST['karyawan_id_pic_sales']);
    $karyawan_id_pic_site = mysqli_real_escape_string($koneksi, $_POST['karyawan_id_pic_site']);

    // Persiapkan nilai tanggal untuk query SQL. Jika kosong, masukkan sebagai NULL.
    $tanggal_mulai_sql = !empty($tanggal_mulai) ? "'$tanggal_mulai'" : "NULL";

    // Buat query INSERT untuk memasukkan data ke tabel 'proyek'
    $query_tambah = "INSERT INTO proyek (
                        id_proyek, nama_proyek, deskripsi, total_biaya_proyek, alamat,
                        tanggal_mulai, status, brand_id_brand, karyawan_id_pic_sales,
                        karyawan_id_pic_site, klien_id_klien
                    ) VALUES (
                        '$id_proyek', '$nama_proyek', '$deskripsi', '$total_biaya_proyek', '$alamat',
                        $tanggal_mulai_sql, '$status', '$brand_id_brand', '$karyawan_id_pic_sales',
                        '$karyawan_id_pic_site', '$klien_id_klien'
                    )";

    // Eksekusi query
    $hasil_tambah = mysqli_query($koneksi, $query_tambah);

    // Arahkan kembali ke halaman proyek dengan pesan status
    if ($hasil_tambah) {
        header('Location: ../proyek.php?pesan=tambah_sukses');
    } else {
        // Jika gagal, tampilkan error (untuk debugging)
        // header('Location: ../proyek.php?pesan=tambah_gagal&error=' . urlencode(mysqli_error($koneksi)));
        header('Location: ../proyek.php?pesan=tambah_gagal');
    }
    exit();
}

//======================================================================
// LOGIKA UNTUK MENGUPDATE PROYEK
//======================================================================
if (isset($_POST['edit_proyek'])) {
    
    // Ambil semua data dari form edit
    $id_proyek = mysqli_real_escape_string($koneksi, $_POST['id_proyek']);
    $nama_proyek = mysqli_real_escape_string($koneksi, $_POST['nama_proyek']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $total_biaya_proyek = mysqli_real_escape_string($koneksi, $_POST['total_biaya_proyek']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $klien_id_klien = mysqli_real_escape_string($koneksi, $_POST['klien_id_klien']);
    $brand_id_brand = mysqli_real_escape_string($koneksi, $_POST['brand_id_brand']);
    $karyawan_id_pic_sales = mysqli_real_escape_string($koneksi, $_POST['karyawan_id_pic_sales']);
    $karyawan_id_pic_site = mysqli_real_escape_string($koneksi, $_POST['karyawan_id_pic_site']);

    // Persiapkan nilai tanggal untuk query SQL. Jika kosong, masukkan sebagai NULL.
    $tanggal_mulai_sql = !empty($tanggal_mulai) ? "'$tanggal_mulai'" : "NULL";

    // Buat query UPDATE
    $query_update = "UPDATE proyek SET
                        nama_proyek = '$nama_proyek',
                        deskripsi = '$deskripsi',
                        total_biaya_proyek = '$total_biaya_proyek',
                        alamat = '$alamat',
                        tanggal_mulai = $tanggal_mulai_sql,
                        status = '$status',
                        brand_id_brand = '$brand_id_brand',
                        karyawan_id_pic_sales = '$karyawan_id_pic_sales',
                        karyawan_id_pic_site = '$karyawan_id_pic_site',
                        klien_id_klien = '$klien_id_klien'
                    WHERE
                        id_proyek = '$id_proyek'";

    // Eksekusi query
    $hasil_update = mysqli_query($koneksi, $query_update);

    // Arahkan kembali ke halaman proyek dengan pesan status
    if ($hasil_update) {
        header('Location: ../proyek.php?pesan=edit_sukses');
    } else {
        header('Location: ../proyek.php?pesan=edit_gagal');
    }
    exit();
}

//======================================================================
// LOGIKA UNTUK MENGHAPUS PROYEK
//======================================================================
if (isset($_GET['hapus_id'])) {
    $id_proyek_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus_id']);

    // PENTING: Untuk menghindari error foreign key, Anda harus menghapus data
    // yang terhubung di tabel lain terlebih dahulu (contoh: pembayarans, jadwal_proyeks).
    // Kode di bawah ini adalah contoh bagaimana Anda akan melakukannya.
    // mysqli_query($koneksi, "DELETE FROM pembayarans WHERE proyek_id_proyek = '$id_proyek_hapus'");
    // mysqli_query($koneksi, "DELETE FROM jadwal_proyeks WHERE proyek_id_proyek = '$id_proyek_hapus'");

    // Buat query DELETE
    $query_hapus = "DELETE FROM proyek WHERE id_proyek = '$id_proyek_hapus'";

    // Eksekusi query penghapusan
    $hasil_hapus = mysqli_query($koneksi, $query_hapus);

    // Arahkan kembali ke halaman proyek dengan pesan status
    if ($hasil_hapus) {
        header('Location: ../proyek.php?pesan=hapus_sukses');
    } else {
        header('Location: ../proyek.php?pesan=hapus_gagal');
    }
    exit();
}


// Jika tidak ada aksi yang dikenali, kembalikan ke halaman utama proyek
header('Location: ../proyek.php');
exit();

?>