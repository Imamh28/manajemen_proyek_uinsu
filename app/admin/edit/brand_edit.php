<?php
include '../../config/database.php';
include '../templates/header.php';
include '../templates/sidebar.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../brand.php');
    exit();
}
$id_brand = mysqli_real_escape_string($koneksi, $_GET['id']);

$query_brand = "SELECT * FROM brand WHERE id_brand = '$id_brand'";
$hasil_brand = mysqli_query($koneksi, $query_brand);
$brand = mysqli_fetch_assoc($hasil_brand);

if (!$brand) {
    header('Location: ../brand.php?pesan=data_tidak_ditemukan');
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
                        <li class="breadcrumb-item"><a href="../brand.php">Kelola Brand</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Brand</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Brand: <?php echo htmlspecialchars($brand['nama_brand']); ?></h5>
                <hr/>
                <div class="p-4 border rounded">
                    <form class="row g-3" action="../proses/brand_proses.php" method="POST">
                        <input type="hidden" name="id_brand" value="<?php echo $brand['id_brand']; ?>">
                        
                        <div class="col-12">
                            <label for="nama_brand" class="form-label">Nama Brand</label>
                            <input type="text" class="form-control" id="nama_brand" name="nama_brand" value="<?php echo htmlspecialchars($brand['nama_brand']); ?>" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="edit_brand" class="btn btn-primary px-4">Update Brand</button>
                            <a href="../brand.php" class="btn btn-secondary px-4">Batal</a>
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