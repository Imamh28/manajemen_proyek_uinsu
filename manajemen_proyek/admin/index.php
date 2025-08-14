<?php 
// 1. Panggil file konfigurasi & session
include '../config/database.php';

// 2. Panggil Header (termasuk cek sesi)
include 'templates/header.php'; 

// 3. Panggil Sidebar
include 'templates/sidebar.php'; 

// 4. Logika PHP untuk mengambil data dashboard dari database
// Total Proyek
$query_total = mysqli_query($koneksi, "SELECT COUNT(id_proyek) AS total FROM proyek");
$data_total = mysqli_fetch_assoc($query_total);
$total_proyek = $data_total['total'];

// Proyek Berjalan
$query_berjalan = mysqli_query($koneksi, "SELECT COUNT(id_proyek) AS total FROM proyek WHERE status = 'Berjalan'");
$data_berjalan = mysqli_fetch_assoc($query_berjalan);
$proyek_berjalan = $data_berjalan['total'];

// Proyek Selesai
$query_selesai = mysqli_query($koneksi, "SELECT COUNT(id_proyek) AS total FROM proyek WHERE status = 'Selesai'");
$data_selesai = mysqli_fetch_assoc($query_selesai);
$proyek_selesai = $data_selesai['total'];

// Menunggu Pembayaran DP
$query_dp = mysqli_query($koneksi, "SELECT COUNT(DISTINCT p.id_proyek) AS total FROM proyek p JOIN pembayarans pb ON p.id_proyek = pb.proyek_id_proyek WHERE pb.jenis_pembayaran = 'DP' AND pb.status_pembayaran = 'Belum Lunas'");
$data_dp = mysqli_fetch_assoc($query_dp);
$menunggu_dp = $data_dp['total'];

// Menunggu Pembayaran Termin
$query_termin = mysqli_query($koneksi, "SELECT COUNT(DISTINCT p.id_proyek) AS total FROM proyek p JOIN pembayarans pb ON p.id_proyek = pb.proyek_id_proyek WHERE pb.jenis_pembayaran = 'Termin' AND pb.status_pembayaran = 'Belum Lunas'");
$data_termin = mysqli_fetch_assoc($query_termin);
$menunggu_termin = $data_termin['total'];

// Menunggu Pembayaran Pelunasan
$query_lunas = mysqli_query($koneksi, "SELECT COUNT(DISTINCT p.id_proyek) AS total FROM proyek p JOIN pembayarans pb ON p.id_proyek = pb.proyek_id_proyek WHERE pb.jenis_pembayaran = 'Pelunasan' AND pb.status_pembayaran = 'Belum Lunas'");
$data_lunas = mysqli_fetch_assoc($query_lunas);
$menunggu_lunas = $data_lunas['total'];
?>

<div class="page-content-wrapper">
  <div class="page-content">

    <div class="row row-cols-1 row-cols-lg-2 row-cols-xxl-4">
      <div class="col">
        <div class="card radius-10">
          <div class="card-body ps-4">
            <div class="d-flex align-items-start gap-2">
              <div><h3 class="mb-0 fs-6">Total Proyek</h3></div>
              <div class="ms-auto widget-icon-small text-white bg-gradient-purple"><ion-icon name="briefcase-outline"></ion-icon></div>
            </div>
            <div class="d-flex align-items-center mt-1">
              <div><h1 class="mb-3"><?php echo $total_proyek; ?></h1></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card radius-10">
          <div class="card-body ps-4">
            <div class="d-flex align-items-start gap-2">
              <div><h3 class="mb-0 fs-6">Proyek Berjalan</h3></div>
              <div class="ms-auto widget-icon-small text-white bg-gradient-warning"><ion-icon name="build-outline"></ion-icon></div>
            </div>
            <div class="d-flex align-items-center mt-1">
              <div><h1 class="mb-3"><?php echo $proyek_berjalan; ?></h1></div>
            </div>
          </div>
        </div>
      </div>
       <div class="col">
        <div class="card radius-10">
          <div class="card-body ps-4">
            <div class="d-flex align-items-start gap-2">
              <div><h3 class="mb-0 fs-6">Proyek Selesai</h3></div>
              <div class="ms-auto widget-icon-small text-white bg-gradient-success"><ion-icon name="checkmark-done-outline"></ion-icon></div>
            </div>
            <div class="d-flex align-items-center mt-1">
              <div><h1 class="mb-3"><?php echo $proyek_selesai; ?></h1></div>
            </div>
          </div>
        </div>
      </div>
       <div class="col">
        <div class="card radius-10">
          <div class="card-body ps-4">
            <div class="d-flex align-items-start gap-2">
              <div><h3 class="mb-0 fs-6">Menunggu Pembayaran DP</h3></div>
              <div class="ms-auto widget-icon-small text-white bg-gradient-danger"><ion-icon name="hourglass-outline"></ion-icon></div>
            </div>
            <div class="d-flex align-items-center mt-1">
              <div><h1 class="mb-3"><?php echo $menunggu_dp; ?></h1></div>
            </div>
          </div>
        </div>
      </div>
    </div><div class="row row-cols-1 row-cols-lg-2 row-cols-xxl-4">
      <div class="col">
        <div class="card radius-10">
          <div class="card-body ps-4">
            <div class="d-flex align-items-start gap-2">
              <div>
                <h3 class="mb-0 fs-6">Menunggu Pembayaran Termin</h3>
              </div>
              <div class="ms-auto widget-icon-small text-white bg-gradient-danger"><ion-icon name="time-outline"></ion-icon></div>
            </div>
            <div class="d-flex align-items-center mt-1">
              <div><h1 class="mb-3"><?php echo $menunggu_termin; ?></h1></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card radius-10">
          <div class="card-body ps-4">
            <div class="d-flex align-items-start gap-2">
              <div><h3 class="mb-0 fs-6">Menunggu Pelunasan</h3></div>
              <div class="ms-auto widget-icon-small text-white bg-gradient-danger"><ion-icon name="alert-circle-outline"></ion-icon></div>
            </div>
            <div class="d-flex align-items-center mt-1">
              <div><h1 class="mb-3"><?php echo $menunggu_lunas; ?></h1></div>
            </div>
          </div>
        </div>
      </div>
    </div></div>
  </div>
<?php 
// 5. Panggil Footer
include 'templates/footer.php'; 
?>