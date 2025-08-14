<?php
include 'config/database.php';
include 'templates/header.php';
include 'templates/sidebar.php';

// --- LOGIKA PENCARIAN ---
$search_keyword = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

$query_brand = "SELECT * FROM brand";

if (!empty($search_keyword)) {
    $query_brand .= " WHERE nama_brand LIKE '%$search_keyword%'";
}

$query_brand .= " ORDER BY id_brand ASC";
$hasil_brand = mysqli_query($koneksi, $query_brand);
?>

<div class="page-content-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Pengaturan</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 align-items-center">
                        <li class="breadcrumb-item"><a href="index.php"><ion-icon name="home-outline"></ion-icon></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kelola Brand</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tambah Brand Baru</h5>
                <hr/>
                <div class="p-4 border rounded">
                    <form class="row g-3" action="proses/brand_proses.php" method="POST">
                        <div class="col-md-12">
                            <label for="nama_brand" class="form-label">Nama Brand</label>
                            <input type="text" class="form-control" id="nama_brand" name="nama_brand" placeholder="Masukkan Nama Brand" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="tambah_brand" class="btn btn-primary px-4">Tambah Brand</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h5 class="card-title">Daftar Brand</h5>
                    </div>
                    <div class="ms-auto">
                        <form action="brand.php" method="GET" class="d-flex">
                            <input type="text" class="form-control" name="search" placeholder="Cari nama brand..." value="<?php echo htmlspecialchars($search_keyword); ?>" style="width: 250px;">
                            <button type="submit" class="btn btn-primary ms-2">Cari</button>
                        </form>
                    </div>
                </div>
                <hr/>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nama Brand</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($hasil_brand) > 0): ?>
                                <?php while($brand = mysqli_fetch_assoc($hasil_brand)): ?>
                                <tr>
                                    <td><?php echo $brand['id_brand']; ?></td>
                                    <td><?php echo htmlspecialchars($brand['nama_brand']); ?></td>
                                    <td>
                                        <div class="d-flex order-actions">
                                            <a href="edit/brand_edit.php?id=<?php echo $brand['id_brand']; ?>" class="ms-3"><ion-icon name="create-outline"></ion-icon></a>
                                            <a href="proses/brand_proses.php?hapus_id=<?php echo $brand['id_brand']; ?>" class="ms-3" onclick="return confirm('Apakah Anda yakin ingin menghapus brand ini?');"><ion-icon name="trash-outline"></ion-icon></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">
                                        Data brand tidak ditemukan.
                                    </td>
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