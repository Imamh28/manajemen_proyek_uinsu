<?php
// 1. Mulai sesi dan panggil file konfigurasi
session_start();
include '../../config/database.php'; // Path disesuaikan menjadi ../../

// 2. Ambil ID proyek dari URL dan validasi
if (!isset($_GET['id'])) {
    // Jika tidak ada ID, redirect ke halaman utama
    header('Location: ../proyek.php');
    exit();
}
$id_proyek = mysqli_real_escape_string($koneksi, $_GET['id']);

// 3. Query untuk mengambil data proyek yang akan diedit
$query_data_proyek = "SELECT * FROM proyek WHERE id_proyek = '$id_proyek'";
$hasil_data_proyek = mysqli_query($koneksi, $query_data_proyek);
$proyek = mysqli_fetch_assoc($hasil_data_proyek);

// Jika data dengan ID tersebut tidak ditemukan, redirect
if (!$proyek) {
    header('Location: ../proyek.php?pesan=data_tidak_ditemukan');
    exit();
}

// 4. Mengambil data untuk mengisi pilihan dropdown (sama seperti di proyek.php)
$klien_list = mysqli_query($koneksi, "SELECT id_klien, nama_klien FROM klien");
$brand_list = mysqli_query($koneksi, "SELECT id_brand, nama_brand FROM brand");
$karyawan_list = mysqli_query($koneksi, "SELECT id_karyawan, nama_karyawan FROM karyawan");

// 5. Panggil header dan sidebar
// Path disesuaikan karena file ini ada di dalam folder 'edit'
include '../templates/header.php';
include '../templates/sidebar.php';
?>

<div class="page-content-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 align-items-center">
                        <li class="breadcrumb-item"><a href="../index.php">
                                <ion-icon name="home-outline"></ion-icon>
                            </a>
                        </li>
                        <li class="breadcrumb-item"><a href="../proyek.php">Manajemen Proyek</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Proyek</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Proyek: <?php echo htmlspecialchars($proyek['nama_proyek']); ?></h5>
                <hr/>
                <div class="p-4 border rounded">
                    <form class="row g-3" action="../proses/proyek_proses.php" method="POST">
                        
                        <div class="col-md-6">
                            <label for="id_proyek_display" class="form-label">ID Proyek</label>
                            <input type="text" class="form-control" id="id_proyek_display" value="<?php echo htmlspecialchars($proyek['id_proyek']); ?>" disabled>
                            <input type="hidden" name="id_proyek" value="<?php echo htmlspecialchars($proyek['id_proyek']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="nama_proyek" class="form-label">Nama Proyek</label>
                            <input type="text" class="form-control" id="nama_proyek" name="nama_proyek" value="<?php echo htmlspecialchars($proyek['nama_proyek']); ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo htmlspecialchars($proyek['deskripsi']); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="total_biaya_proyek" class="form-label">Total Biaya Proyek (Rp)</label>
                            <input type="number" class="form-control" id="total_biaya_proyek" name="total_biaya_proyek" value="<?php echo htmlspecialchars($proyek['total_biaya_proyek']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="alamat" class="form-label">Alamat Proyek</label>
                            <input type="text" class="form-control" id="alamat" name="alamat" value="<?php echo htmlspecialchars($proyek['alamat']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo htmlspecialchars($proyek['tanggal_mulai']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status Proyek</label>
                            <select id="status" name="status" class="form-select">
                                <?php $status_options = ['Menunggu', 'Berjalan', 'Selesai', 'Dibatalkan']; ?>
                                <?php foreach ($status_options as $option) : ?>
                                    <option value="<?php echo $option; ?>" <?php if ($proyek['status'] == $option) echo 'selected'; ?>>
                                        <?php echo $option; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                         <div class="col-md-6">
                            <label for="klien_id_klien" class="form-label">Klien</label>
                            <select id="klien_id_klien" name="klien_id_klien" class="form-select" required>
                                <option disabled value="">Pilih Klien...</option>
                                <?php while($klien = mysqli_fetch_assoc($klien_list)): ?>
                                    <option value="<?php echo $klien['id_klien']; ?>" <?php if ($proyek['klien_id_klien'] == $klien['id_klien']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($klien['nama_klien']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="brand_id_brand" class="form-label">Brand</label>
                            <select id="brand_id_brand" name="brand_id_brand" class="form-select" required>
                                <option disabled value="">Pilih Brand...</option>
                                 <?php while($brand = mysqli_fetch_assoc($brand_list)): ?>
                                    <option value="<?php echo $brand['id_brand']; ?>" <?php if ($proyek['brand_id_brand'] == $brand['id_brand']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($brand['nama_brand']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="karyawan_id_pic_sales" class="form-label">PIC Sales</label>
                            <select id="karyawan_id_pic_sales" name="karyawan_id_pic_sales" class="form-select" required>
                                <option disabled value="">Pilih PIC Sales...</option>
                                 <?php mysqli_data_seek($karyawan_list, 0); // Reset pointer
                                 while($karyawan = mysqli_fetch_assoc($karyawan_list)): ?>
                                    <option value="<?php echo $karyawan['id_karyawan']; ?>" <?php if ($proyek['karyawan_id_pic_sales'] == $karyawan['id_karyawan']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($karyawan['nama_karyawan']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="karyawan_id_pic_site" class="form-label">PIC Site</label>
                            <select id="karyawan_id_pic_site" name="karyawan_id_pic_site" class="form-select" required>
                                <option disabled value="">Pilih PIC Site...</option>
                                 <?php mysqli_data_seek($karyawan_list, 0); // Reset pointer
                                 while($karyawan = mysqli_fetch_assoc($karyawan_list)): ?>
                                    <option value="<?php echo $karyawan['id_karyawan']; ?>" <?php if ($proyek['karyawan_id_pic_site'] == $karyawan['id_karyawan']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($karyawan['nama_karyawan']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="edit_proyek" class="btn btn-primary px-4">Update Proyek</button>
                            <a href="../proyek.php" class="btn btn-secondary px-4">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
<?php
// 6. Panggil footer
include '../templates/footer.php';
?>