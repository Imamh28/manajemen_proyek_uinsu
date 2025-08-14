<?php
include '../../config/database.php';
include '../templates/header.php';
include '../templates/sidebar.php';

if (!isset($_GET['id'])) {
    header('Location: ../klien.php');
    exit();
}
$id_klien = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data klien yang akan diedit
$query_klien = "SELECT * FROM klien WHERE id_klien = '$id_klien'";
$hasil_klien = mysqli_query($koneksi, $query_klien);
$klien = mysqli_fetch_assoc($hasil_klien);

if (!$klien) {
    header('Location: ../klien.php?pesan=data_tidak_ditemukan');
    exit();
}
?>

<div class="page-content-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Proyek & Klien</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 align-items-center">
                        <li class="breadcrumb-item"><a href="../index.php"><ion-icon name="home-outline"></ion-icon></a></li>
                        <li class="breadcrumb-item"><a href="../klien.php">Kelola Klien</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Klien</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Klien: <?php echo htmlspecialchars($klien['nama_klien']); ?></h5>
                <hr/>
                <div class="p-4 border rounded">
                    <form class="row g-3" action="../proses/klien_proses.php" method="POST">
                        <input type="hidden" name="id_klien" value="<?php echo htmlspecialchars($klien['id_klien']); ?>">
                        
                        <div class="col-md-6">
                            <label for="nama_klien" class="form-label">Nama Klien</label>
                            <input type="text" class="form-control" id="nama_klien" name="nama_klien" value="<?php echo htmlspecialchars($klien['nama_klien']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="no_telepon_klien" class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" id="no_telepon_klien" name="no_telepon_klien" value="<?php echo htmlspecialchars($klien['no_telepon_klien']); ?>">
                        </div>
                        <div class="col-12">
                            <label for="email_klien" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email_klien" name="email_klien" value="<?php echo htmlspecialchars($klien['email_klien']); ?>">
                        </div>
                        <div class="col-12">
                            <label for="alamat_klien" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat_klien" name="alamat_klien" rows="3"><?php echo htmlspecialchars($klien['alamat_klien']); ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="edit_klien" class="btn btn-primary px-4">Update Klien</button>
                            <a href="../klien.php" class="btn btn-secondary px-4">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
include '../templates/footer.php';
?>