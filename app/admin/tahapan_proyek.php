<?php
include 'config/database.php';
include 'templates/header.php';
include 'templates/sidebar.php';

// Ambil semua data tahapan untuk ditampilkan di tabel
$query_tahapan = "SELECT * FROM daftar_tahapans ORDER BY id_tahapan ASC";
$hasil_tahapan = mysqli_query($koneksi, $query_tahapan);
?>

<div class="page-content-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Pengaturan</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 align-items-center">
                        <li class="breadcrumb-item"><a href="index.php"><ion-icon name="home-outline"></ion-icon></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kelola Tahapan Proyek</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tambah Tahapan Proyek Baru</h5>
                <hr/>
                <div class="p-4 border rounded">
                    <form class="row g-3" action="proses/tahapan_proses.php" method="POST">
                        <div class="col-md-6">
                            <label for="id_tahapan" class="form-label">ID Tahapan</label>
                            <input type="text" class="form-control" id="id_tahapan" name="id_tahapan" placeholder="Contoh: TH07" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_tahapan" class="form-label">Nama Tahapan</label>
                            <input type="text" class="form-control" id="nama_tahapan" name="nama_tahapan" placeholder="Nama Tahapan Proyek" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="tambah_tahapan" class="btn btn-primary px-4">Tambah Tahapan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Daftar Tahapan Proyek</h5>
                <hr/>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">ID Tahapan</th>
                                <th scope="col">Nama Tahapan</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($hasil_tahapan) > 0): ?>
                                <?php while($tahapan = mysqli_fetch_assoc($hasil_tahapan)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($tahapan['id_tahapan']); ?></td>
                                    <td><?php echo htmlspecialchars($tahapan['nama_tahapan']); ?></td>
                                    <td>
                                        <div class="d-flex order-actions">
                                            <a href="edit/tahapan_edit.php?id=<?php echo $tahapan['id_tahapan']; ?>" class="ms-3"><ion-icon name="create-outline"></ion-icon></a>
                                            <a href="proses/tahapan_proses.php?hapus_id=<?php echo $tahapan['id_tahapan']; ?>" class="ms-3" onclick="return confirm('Apakah Anda yakin ingin menghapus tahapan ini?');"><ion-icon name="trash-outline"></ion-icon></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">Belum ada data tahapan.</td>
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
include 'templates/footer.php';
?>