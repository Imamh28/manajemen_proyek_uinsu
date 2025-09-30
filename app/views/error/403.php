<?php
if (isset($_SESSION['error'])) unset($_SESSION['error']);
$currentRoute = '/' . ltrim($_GET['r'] ?? '', '/');
$roleName     = $_SESSION['user']['role_name'] ?? 'User';
?>

<!-- start page content wrapper-->
<div class="page-content-wrapper">
    <!-- start page content-->
    <div class="page-content">
        <div class="card radius-10">
            <div class="row g-0 align-items-center">
                <div class="col-12 col-xl-5">
                    <div class="card-body">
                        <h1 class="display-1">
                            <span class="text-danger">4</span>
                            <span class="text-primary">0</span>
                            <span class="text-success">3</span>
                        </h1>
                        <h2 class="font-weight-bold display-4">Access Denied!</h2>
                        <p class="mt-2">
                            Anda saat ini masuk sebagai <strong><?= htmlspecialchars($roleName) ?></strong>.
                            <br>Halaman yang Anda coba buka
                            <?php if ($currentRoute && $currentRoute !== '/'): ?>
                                <code><?= htmlspecialchars($currentRoute) ?></code>
                            <?php else: ?>
                                tersebut
                            <?php endif; ?>
                            tidak dapat diakses dengan peran Anda.
                        </p>
                        <p class="mb-0">
                            Silakan kembali ke halaman sebelumnya atau menuju Dashboard.
                            Jika menurut Anda ini keliru, hubungi Administrator untuk penyesuaian hak akses.
                        </p>

                        <div class="hstack gap-2 mt-3">
                            <a href="javascript:void(0)" class="btn btn-outline-secondary" onclick="history.back()" aria-label="Kembali ke halaman sebelumnya">← Kembali</a>
                            <a href="<?= $BASE_URL ?>index.php?r=dashboard" class="btn btn-primary" aria-label="Pergi ke Dashboard">Ke Dashboard</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-7 text-center">
                    <img src="<?= $BASE_URL ?>assets/images/error/403-error.png" class="img-fluid" alt="403 Forbidden">
                </div>
            </div>
            <!--end row-->
        </div>
    </div>
</div>