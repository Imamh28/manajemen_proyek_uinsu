<?php
$currentRoute = '/' . ltrim($_GET['r'] ?? '', '/');
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
                            <span class="text-success">4</span>
                        </h1>
                        <h2 class="font-weight-bold display-4">Page Not Found!</h2>
                        <p class="mt-2">
                            Maaf, kami tidak menemukan halaman yang Anda minta.
                            <?php if ($currentRoute && $currentRoute !== '/'): ?>
                                <br>Rute yang dicoba: <code><?= htmlspecialchars($currentRoute) ?></code>
                            <?php endif; ?>
                            <br>Halaman mungkin telah dipindahkan atau URL tidak tepat.
                        </p>

                        <p class="mb-0">
                            Silakan periksa kembali alamat, kembali ke halaman sebelumnya,
                            atau menuju Dashboard.
                        </p>

                        <div class="hstack gap-2 mt-3">
                            <a href="javascript:void(0)" class="btn btn-outline-secondary" onclick="history.back()" aria-label="Kembali ke halaman sebelumnya">← Kembali</a>
                            <a href="<?= $BASE_URL ?>index.php?r=dashboard" class="btn btn-primary" aria-label="Pergi ke Dashboard">Ke Dashboard</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-7 text-center">
                    <img src="<?= $BASE_URL ?>assets/images/error/404-error.png" class="img-fluid" alt="404 Not Found">
                </div>
            </div>
            <!--end row-->
        </div>
    </div>
</div>