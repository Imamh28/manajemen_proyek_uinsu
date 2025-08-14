<?php
include '../../config/database.php';
include '../templates/header.php';
include '../templates/sidebar.php';

// Validasi dan ambil ID dari URL
if (!isset($_GET['id'])) {
    header('Location: ../tahapan_proyek.php');
    exit();
}
$id_tahapan = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data tahapan yang akan diedit
$query = "SELECT * FROM daftar_tahapans WHERE id_tahapan = '$id_tahapan'";
$hasil = mysqli_query($koneksi, $query);
$tahapan = mysqli_fetch_assoc($hasil);

if (!$tahapan) {
    header('Location: ../tahapan_proyek.php?pesan=data_tidak_ditemukan');
    exit();
}
?>

<div class="page-content-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Pengaturan</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 align-items-center">
                        <li class="breadcrumb-item"><a href="../index.php"><ion-icon name="home-outline"></ion-icon></a></li>
                        <li class="breadcrumb-item"><a href="../tahapan_proyek.php">Kelola Tahapan Proyek</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Tahapan</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Tahapan Proyek</h5>
                <hr/>
                <div class="p-4 border rounded">
                    <form class="row g-3" action="../proses/tahapan_proses.php" method="POST">
                        <input type="hidden" name="id_tahapan_lama" value="<?php echo htmlspecialchars($tahapan['id_tahapan']); ?>">
                        
                        <div class="col-md-6">
                            <label for="id_tahapan" class="form-label">ID Tahapan</label>
                            <input type="text" class="form-control" id="id_tahapan" name="id_tahapan" value="<?php echo htmlspecialchars($tahapan['id_tahapan']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_tahapan" class="form-label">Nama Tahapan</label>
                            <input type="text" class="form-control" id="nama_tahapan" name="nama_tahapan" value="<?php echo htmlspecialchars($tahapan['nama_tahapan']); ?>" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="edit_tahapan" class="btn btn-primary px-4">Update Tahapan</button>
                            <a href="../tahapan_proyek.php" class="btn btn-secondary px-4">Batal</a>
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