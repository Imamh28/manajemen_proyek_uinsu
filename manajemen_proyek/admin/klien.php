<?php
include '../config/database.php';
include 'templates/header.php';
include 'templates/sidebar.php';

// --- LOGIKA PENCARIAN ---
$search_keyword = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

// Query dasar untuk mengambil daftar klien
$query_klien = "SELECT * FROM klien";

// Jika ada kata kunci pencarian, tambahkan kondisi WHERE
if (!empty($search_keyword)) {
    $query_klien .= " WHERE (id_klien LIKE '%$search_keyword%' 
                              OR nama_klien LIKE '%$search_keyword%' 
                              OR email_klien LIKE '%$search_keyword%' 
                              OR no_telepon_klien LIKE '%$search_keyword%')";
}

$query_klien .= " ORDER BY id_klien ASC";
$hasil_klien = mysqli_query($koneksi, $query_klien);
?>

<div class="page-content-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Proyek & Klien</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 align-items-center">
                        <li class="breadcrumb-item"><a href="index.php"><ion-icon name="home-outline"></ion-icon></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kelola Klien</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tambah Klien Baru</h5>
                <hr/>
                <div class="p-4 border rounded">
                    <form class="row g-3" action="proses/klien_proses.php" method="POST">
                        <div class="col-md-6">
                            <label for="id_klien" class="form-label">ID Klien</label>
                            <input type="text" class="form-control" id="id_klien" name="id_klien" placeholder="Contoh: KL003" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_klien" class="form-label">Nama Klien</label>
                            <input type="text" class="form-control" id="nama_klien" name="nama_klien" required>
                        </div>
                        <div class="col-md-6">
                            <label for="no_telepon_klien" class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" id="no_telepon_klien" name="no_telepon_klien">
                        </div>
                        <div class="col-md-6">
                            <label for="email_klien" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email_klien" name="email_klien">
                        </div>
                        <div class="col-12">
                            <label for="alamat_klien" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat_klien" name="alamat_klien" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="tambah_klien" class="btn btn-primary px-4">Tambah Klien</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h5 class="card-title">Daftar Klien</h5>
                    </div>
                    <div class="ms-auto">
                        <form action="klien.php" method="GET" class="d-flex">
                            <input type="text" class="form-control" name="search" placeholder="Cari klien..." value="<?php echo htmlspecialchars($search_keyword); ?>" style="width: 250px;">
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
                                <th scope="col">Nama</th>
                                <th scope="col">Telepon</th>
                                <th scope="col">Email</th>
                                <th scope="col">Alamat</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($hasil_klien) > 0): ?>
                                <?php while($klien = mysqli_fetch_assoc($hasil_klien)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($klien['id_klien']); ?></td>
                                    <td><?php echo htmlspecialchars($klien['nama_klien']); ?></td>
                                    <td><?php echo htmlspecialchars($klien['no_telepon_klien']); ?></td>
                                    <td><?php echo htmlspecialchars($klien['email_klien']); ?></td>
                                    <td><?php echo htmlspecialchars($klien['alamat_klien']); ?></td>
                                    <td>
                                        <div class="d-flex order-actions">
                                            <a href="edit/klien_edit.php?id=<?php echo $klien['id_klien']; ?>" class="ms-3"><ion-icon name="create-outline"></ion-icon></a>
                                            <a href="proses/klien_proses.php?hapus_id=<?php echo $klien['id_klien']; ?>" class="ms-3" onclick="return confirm('Apakah Anda yakin ingin menghapus klien ini?');"><ion-icon name="trash-outline"></ion-icon></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <?php if (!empty($search_keyword)): ?>
                                            Tidak ada klien yang cocok dengan kata kunci "<strong><?php echo htmlspecialchars($search_keyword); ?></strong>".
                                            <br>
                                            <a href="klien.php">Tampilkan Semua Klien</a>
                                        <?php else: ?>
                                            Belum ada data klien.
                                        <?php endif; ?>
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