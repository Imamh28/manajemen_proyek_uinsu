<?php
// 1. Panggil file konfigurasi, header, dan sidebar
include 'config/database.php';
include 'templates/header.php';
include 'templates/sidebar.php';

// 2. Logika PHP untuk mengambil data yang diperlukan
// Mengambil data proyek dengan JOIN untuk mendapatkan nama, bukan hanya ID
$query_proyek = "SELECT
                    p.id_proyek,
                    p.nama_proyek,
                    p.total_biaya_proyek,
                    p.status,
                    k.nama_klien,
                    sales.nama_karyawan AS nama_sales
                 FROM
                    proyek AS p
                 LEFT JOIN
                    klien AS k ON p.klien_id_klien = k.id_klien
                 LEFT JOIN
                    karyawan AS sales ON p.karyawan_id_pic_sales = sales.id_karyawan
                 ORDER BY
                    p.id_proyek DESC";
$hasil_proyek = mysqli_query($koneksi, $query_proyek);

// Mengambil data untuk form dropdown
$klien_list = mysqli_query($koneksi, "SELECT id_klien, nama_klien FROM klien");
$brand_list = mysqli_query($koneksi, "SELECT id_brand, nama_brand FROM brand");
$karyawan_list = mysqli_query($koneksi, "SELECT id_karyawan, nama_karyawan FROM karyawan");

?>

<div class="page-content-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 align-items-center">
                        <li class="breadcrumb-item"><a href="index.php">
                                <ion-icon name="home-outline"></ion-icon>
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Manajemen Proyek</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tambah Proyek Baru</h5>
                <hr/>
                <div class="p-4 border rounded">
                    <form class="row g-3" action="proses/proyek_proses.php" method="POST" enctype="multipart/form-data">
                        <div class="col-md-6">
                            <label for="id_proyek" class="form-label">ID Proyek</label>
                            <input type="text" class="form-control" id="id_proyek" name="id_proyek" placeholder="Contoh: PRJ003" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_proyek" class="form-label">Nama Proyek</label>
                            <input type="text" class="form-control" id="nama_proyek" name="nama_proyek" required>
                        </div>
                        <div class="col-12">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="total_biaya_proyek" class="form-label">Total Biaya Proyek (Rp)</label>
                            <input type="number" class="form-control" id="total_biaya_proyek" name="total_biaya_proyek" placeholder="Contoh: 50000000" required>
                        </div>
                        <div class="col-md-6">
                            <label for="alamat" class="form-label">Alamat Proyek</label>
                            <input type="text" class="form-control" id="alamat" name="alamat">
                        </div>
                        <div class="col-md-6">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai">
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status Proyek</label>
                            <select id="status" name="status" class="form-select">
                                <option value="Menunggu" selected>Menunggu</option>
                                <option value="Berjalan">Berjalan</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                         <div class="col-md-6">
                            <label for="klien_id_klien" class="form-label">Klien</label>
                            <select id="klien_id_klien" name="klien_id_klien" class="form-select" required>
                                <option selected disabled value="">Pilih Klien...</option>
                                <?php while($klien = mysqli_fetch_assoc($klien_list)): ?>
                                    <option value="<?php echo $klien['id_klien']; ?>"><?php echo htmlspecialchars($klien['nama_klien']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="brand_id_brand" class="form-label">Brand</label>
                            <select id="brand_id_brand" name="brand_id_brand" class="form-select" required>
                                <option selected disabled value="">Pilih Brand...</option>
                                 <?php while($brand = mysqli_fetch_assoc($brand_list)): ?>
                                    <option value="<?php echo $brand['id_brand']; ?>"><?php echo htmlspecialchars($brand['nama_brand']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="karyawan_id_pic_sales" class="form-label">PIC Sales</label>
                            <select id="karyawan_id_pic_sales" name="karyawan_id_pic_sales" class="form-select" required>
                                <option selected disabled value="">Pilih PIC Sales...</option>
                                 <?php mysqli_data_seek($karyawan_list, 0); // Reset pointer
                                 while($karyawan = mysqli_fetch_assoc($karyawan_list)): ?>
                                    <option value="<?php echo $karyawan['id_karyawan']; ?>"><?php echo htmlspecialchars($karyawan['nama_karyawan']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="karyawan_id_pic_site" class="form-label">PIC Site</label>
                            <select id="karyawan_id_pic_site" name="karyawan_id_pic_site" class="form-select" required>
                                <option selected disabled value="">Pilih PIC Site...</option>
                                 <?php mysqli_data_seek($karyawan_list, 0); // Reset pointer
                                 while($karyawan = mysqli_fetch_assoc($karyawan_list)): ?>
                                    <option value="<?php echo $karyawan['id_karyawan']; ?>"><?php echo htmlspecialchars($karyawan['nama_karyawan']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="tambah_proyek" class="btn btn-primary px-4">Tambah Proyek</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Daftar Proyek</h5>
                 <hr/>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nama Proyek</th>
                                <th scope="col">Klien</th>
                                <th scope="col">Total Biaya</th>
                                <th scope="col">Status</th>
                                <th scope="col">PIC Sales</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($hasil_proyek) > 0): ?>
                                <?php while($proyek = mysqli_fetch_assoc($hasil_proyek)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($proyek['id_proyek']); ?></td>
                                    <td><?php echo htmlspecialchars($proyek['nama_proyek']); ?></td>
                                    <td><?php echo htmlspecialchars($proyek['nama_klien']); ?></td>
                                    <td>Rp <?php echo number_format($proyek['total_biaya_proyek'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php 
                                            $status = $proyek['status'];
                                            $badge_class = 'bg-secondary'; // default
                                            if($status == 'Berjalan') $badge_class = 'bg-warning';
                                            if($status == 'Selesai') $badge_class = 'bg-success';
                                            if($status == 'Menunggu') $badge_class = 'bg-info';
                                            if($status == 'Dibatalkan') $badge_class = 'bg-danger';
                                            echo "<span class='badge $badge_class'>$status</span>";
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($proyek['nama_sales']); ?></td>
                                    <td>
                                        <div class="d-flex order-actions">
                                            <a href="edit/proyek_edit.php?id=<?php echo $proyek['id_proyek']; ?>" class="ms-3"><ion-icon name="create-outline"></ion-icon></a>
                                            <a href="proses/proyek_proses.php?hapus_id=<?php echo $proyek['id_proyek']; ?>" class="ms-3" onclick="return confirm('Apakah Anda yakin ingin menghapus proyek ini?');"><ion-icon name="trash-outline"></ion-icon></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada data proyek.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
<?php
// 3. Panggil file footer
include 'templates/footer.php';
?>