<?php
require_once __DIR__ . '/config/init.php';
require_once __DIR__ . '/middleware/auth.php';          // memastikan sudah login
require_once __DIR__ . '/middleware/authorize.php';     // guard akses by URL/role
require_once __DIR__ . '/utils/roles.php';              // map role -> folder view
require_once __DIR__ . '/utils/router.php';             // dispatch_route()

// ====== TANGANI AKSI SEBELUM OUTPUT (PENTING) ======
if (handle_actions($BASE_URL)) {
    exit;
}     // <-- TAMBAHKAN BARIS INI
// ===================================================

// (opsional) komponen modal global
include_once __DIR__ . '/partials/modal.php';
?>
<!doctype html>
<html lang="en">

<head>
    <?php include 'partials/header.php'; ?>
</head>

<body>
    <!--start wrapper-->
    <div class="wrapper">
        <?php include 'partials/sidebar.php'; ?>
        <?php include 'partials/navbar.php'; ?>

        <!-- area konten utama -->
        <main class="page-content">
            <?php
            // Router akan:
            // - cek izin akses berdasar tabel menus/role_menu (via require_menu_access)
            // - pilih file view sesuai role (views/admin|projek_manajer|mandor/...)
            // - fallback ke views/shared atau 404 bila perlu
            dispatch_route($BASE_URL);
            ?>
        </main>

        <?php include 'partials/footer.php'; ?>

        <!--Start Back To Top Button-->
        <a href="javascript:;" class="back-to-top">
            <ion-icon name="arrow-up-outline"></ion-icon>
        </a>
        <!--End Back To Top Button-->

        <?php include 'partials/customization.php'; ?>
        <!--start overlay-->
        <div class="overlay nav-toggle-icon"></div>
        <!--end overlay-->
    </div>
    <!--end wrapper-->

    <?php
    // Modal logout global (tetap di luar navbar)
    if (function_exists('renderModal')) {
        renderModal(
            'logoutModal',
            'Konfirmasi Logout',
            'Apakah kamu yakin ingin keluar dari aplikasi?',
            'danger',
            'Batal',
            $BASE_URL . 'auth/logout.php'
        );
    }
    ?>

    <?php include 'partials/script.php'; ?>
</body>

</html>