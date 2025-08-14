<?php
include '../config/database.php';
include 'templates/header.php';
include 'templates/sidebar.php';

// Ambil daftar role untuk dropdown
$query_role = "SELECT * FROM role ORDER BY nama_role ASC";
$hasil_role = mysqli_query($koneksi, $query_role);

// --- LOGIKA PENCARIAN ---
// Ambil kata kunci pencarian dari URL jika ada
$search_keyword = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

// Query dasar untuk mengambil daftar karyawan
$query_karyawan = "SELECT k.id_karyawan, k.nama_karyawan, k.email, k.no_telepon_karyawan, r.nama_role 
                   FROM karyawan k 
                   JOIN role r ON k.role_id_role = r.id_role";

// Jika ada kata kunci pencarian, tambahkan kondisi WHERE
if (!empty($search_keyword)) {
    $query_karyawan .= " WHERE (k.id_karyawan LIKE '%$search_keyword%' 
                               OR k.nama_karyawan LIKE '%$search_keyword%' 
                               OR k.email LIKE '%$search_keyword%' 
                               OR k.no_telepon_karyawan LIKE '%$search_keyword%')";
}

$query_karyawan .= " ORDER BY k.id_karyawan ASC";
$hasil_karyawan = mysqli_query($koneksi, $query_karyawan);
?>

<div class="page-content-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Pengaturan</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 align-items-center">
                        <li class="breadcrumb-item"><a href="index.php"><ion-icon name="home-outline"></ion-icon></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kelola Karyawan</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tambah Karyawan Baru</h5>
                <hr/>
                <div class="p-4 border rounded">
                    <form class="row g-3" action="proses/karyawan_proses.php" method="POST">
                        <div class="col-md-6">
                            <label for="id_karyawan" class="form-label">ID Karyawan</label>
                            <input type="text" class="form-control" id="id_karyawan" name="id_karyawan" placeholder="Contoh: KR005" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_karyawan" class="form-label">Nama Karyawan</label>
                            <input type="text" class="form-control" id="nama_karyawan" name="nama_karyawan" required>
                        </div>
                        <div class="col-md-6">
                            <label for="no_telepon_karyawan" class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" id="no_telepon_karyawan" name="no_telepon_karyawan">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="role_id_role" class="form-label">Role</label>
                            <select id="role_id_role" name="role_id_role" class="form-select" required>
                                <option selected disabled value="">Pilih Role...</option>
                                <?php mysqli_data_seek($hasil_role, 0); // Reset pointer hasil_role ?>
                                <?php while($role = mysqli_fetch_assoc($hasil_role)): ?>
                                    <option value="<?php echo $role['id_role']; ?>"><?php echo htmlspecialchars($role['nama_role']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="tambah_karyawan" class="btn btn-primary px-4">Tambah Karyawan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h5 class="card-title">Daftar Karyawan</h5>
                    </div>
                    <div class="ms-auto">
                        <form action="karyawan.php" method="GET" class="d-flex">
                            <input type="text" class="form-control" name="search" placeholder="Cari karyawan..." value="<?php echo htmlspecialchars($search_keyword); ?>" style="width: 250px;">
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
                                <th scope="col">Email</th>
                                <th scope="col">Telepon</th>
                                <th scope="col">Role</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($hasil_karyawan) > 0): ?>
                                <?php while($karyawan = mysqli_fetch_assoc($hasil_karyawan)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($karyawan['id_karyawan']); ?></td>
                                    <td><?php echo htmlspecialchars($karyawan['nama_karyawan']); ?></td>
                                    <td><?php echo htmlspecialchars($karyawan['email']); ?></td>
                                    <td><?php echo htmlspecialchars($karyawan['no_telepon_karyawan']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($karyawan['nama_role']); ?></span></td>
                                    <td>
                                        <div class="d-flex order-actions">
                                            <a href="edit/karyawan_edit.php?id=<?php echo $karyawan['id_karyawan']; ?>" class="ms-3"><ion-icon name="create-outline"></ion-icon></a>
                                            <a href="proses/karyawan_proses.php?hapus_id=<?php echo $karyawan['id_karyawan']; ?>" class="ms-3" onclick="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?');"><ion-icon name="trash-outline"></ion-icon></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <?php if (!empty($search_keyword)): ?>
                                            Tidak ada karyawan yang cocok dengan kata kunci "<strong><?php echo htmlspecialchars($search_keyword); ?></strong>".
                                            <br>
                                            <a href="karyawan.php">Tampilkan Semua Karyawan</a>
                                        <?php else: ?>
                                            Belum ada data karyawan.
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