<?php
include '../../config/database.php';
include '../templates/header.php';
include '../templates/sidebar.php';

if (!isset($_GET['id'])) {
    header('Location: ../karyawan.php');
    exit();
}
$id_karyawan = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data karyawan yang akan diedit
$query_karyawan = "SELECT * FROM karyawan WHERE id_karyawan = '$id_karyawan'";
$hasil_karyawan = mysqli_query($koneksi, $query_karyawan);
$karyawan = mysqli_fetch_assoc($hasil_karyawan);

if (!$karyawan) {
    header('Location: ../karyawan.php?pesan=data_tidak_ditemukan');
    exit();
}

// Ambil daftar role untuk dropdown
$query_role = "SELECT * FROM role ORDER BY nama_role ASC";
$hasil_role = mysqli_query($koneksi, $query_role);
?>

<div class="page-content-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Pengaturan</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 align-items-center">
                        <li class="breadcrumb-item"><a href="../index.php"><ion-icon name="home-outline"></ion-icon></a></li>
                        <li class="breadcrumb-item"><a href="../karyawan.php">Kelola Karyawan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Karyawan</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Karyawan: <?php echo htmlspecialchars($karyawan['nama_karyawan']); ?></h5>
                <hr/>
                <div class="p-4 border rounded">
                    <form class="row g-3" action="../proses/karyawan_proses.php" method="POST">
                        <input type="hidden" name="id_karyawan" value="<?php echo htmlspecialchars($karyawan['id_karyawan']); ?>">
                        
                        <div class="col-md-6">
                            <label for="nama_karyawan" class="form-label">Nama Karyawan</label>
                            <input type="text" class="form-control" id="nama_karyawan" name="nama_karyawan" value="<?php echo htmlspecialchars($karyawan['nama_karyawan']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="no_telepon_karyawan" class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" id="no_telepon_karyawan" name="no_telepon_karyawan" value="<?php echo htmlspecialchars($karyawan['no_telepon_karyawan']); ?>">
                        </div>
                        <div class="col-md-12">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($karyawan['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Kosongkan jika tidak ingin diubah">
                        </div>
                        <div class="col-md-6">
                            <label for="role_id_role" class="form-label">Role</label>
                            <select id="role_id_role" name="role_id_role" class="form-select" required>
                                <?php while($role = mysqli_fetch_assoc($hasil_role)): ?>
                                    <option value="<?php echo $role['id_role']; ?>" <?php if($karyawan['role_id_role'] == $role['id_role']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($role['nama_role']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="edit_karyawan" class="btn btn-primary px-4">Update Karyawan</button>
                            <a href="../karyawan.php" class="btn btn-secondary px-4">Batal</a>
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