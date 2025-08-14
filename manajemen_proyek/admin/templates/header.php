<?php
// Pastikan sesi dan koneksi sudah di-include di file utama yang memanggil header ini.
// Contoh: di index.php atau karyawan.php, baris paling atas adalah include '../config/database.php';
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../login.php?pesan=belum_login");
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-t">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="../assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
  <link href="../assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
  <link href="../assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet" />
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="../assets/css/icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <title>Manajemen Proyek</title>
</head>
<body>
  <div class="wrapper">
    <header class="top-header">
      <nav class="navbar navbar-expand gap-3">
        <div class="toggle-icon">
          <ion-icon name="menu-outline"></ion-icon>
        </div>
        <div class="top-navbar-right ms-auto">
          <ul class="navbar-nav align-items-center">
            <li class="nav-item dropdown dropdown-user-setting">
              <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
                <div class="user-setting">
                  <img src="../assets/images/avatars/06.png" class="user-img" alt="">
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                   <a class="dropdown-item" href="#">
                     <div class="d-flex flex-row align-items-center gap-2">
                       <img src="../assets/images/avatars/06.png" alt="" class="rounded-circle" width="54" height="54">
                       <div class="">
                         <h6 class="mb-0 dropdown-user-name"><?php echo htmlspecialchars($_SESSION['nama_karyawan']); ?></h6>
                         <small class="mb-0 dropdown-user-designation text-secondary"><?php echo htmlspecialchars($_SESSION['role']); ?></small>
                       </div>
                     </div>
                   </a>
                 </li>
                 <li><hr class="dropdown-divider"></li>
                 <li>
                   <a class="dropdown-item" href="../logout.php">
                     <div class="d-flex align-items-center">
                       <div class=""><ion-icon name="log-out-outline"></ion-icon></div>
                       <div class="ms-3"><span>Logout</span></div>
                     </div>
                   </a>
                 </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
    </header>