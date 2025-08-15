<?php
require_once __DIR__ . '/config/init.php';
require_once __DIR__ . '/middleware/auth.php';
include_once __DIR__ . '/partials/modal.php';
?>
<!doctype html>
<html lang="en">

<head>
    <?php include 'partials/header.php'; ?>
</head>

<!-- IMPORTANT, besides the login page always provide this code -->
<!-- <script>
    window.addEventListener("unload", function() {
        navigator.sendBeacon('/auth/logout.php');
    });
</script> -->

<body>
    <!--start wrapper-->
    <div class="wrapper">
        <?php include 'partials/sidebar.php'; ?>
        <?php include 'partials/navbar.php'; ?>
        <?php include 'views/dashboard.php'; ?>
        <?php include 'partials/footer.php'; ?>
        <!--Start Back To Top Button-->
        <a href="javaScript:;" class="back-to-top">
            <ion-icon name="arrow-up-outline"></ion-icon>
        </a>
        <!--End Back To Top Button-->
        <?php include 'partials/customization.php'; ?>
        <!--start overlay-->
        <div class="overlay nav-toggle-icon"></div>
        <!--end overlay-->
    </div>
    <!--end wrapper-->
    <?php include 'partials/script.php'; ?>
</body>

</html>