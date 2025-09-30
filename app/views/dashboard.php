<?php
// views/(shared|role)/dashboard.php
// --- ambil PDO global dari init.php ---
/** @var PDO $pdo */
$pdo = $GLOBALS['pdo'] ?? null;
if (!$pdo) {
    echo '<div class="alert alert-danger m-3">Koneksi DB tidak tersedia.</div>';
    return;
}

// ===== hitung proyek =====
$totalProyek     = (int)$pdo->query("SELECT COUNT(*) FROM proyek")->fetchColumn();
$proyekBerjalan  = (int)$pdo->query("SELECT COUNT(*) FROM proyek WHERE status='Berjalan'")->fetchColumn();
$proyekSelesai   = (int)$pdo->query("SELECT COUNT(*) FROM proyek WHERE status='Selesai'")->fetchColumn();

// ===== hitung pembayaran yang masih "Belum Lunas" per jenis =====
$qCount = fn(string $jenis) => (int)$pdo
    ->query("SELECT COUNT(*) FROM pembayarans WHERE jenis_pembayaran='$jenis' AND status_pembayaran='Belum Lunas'")
    ->fetchColumn();

$menungguDP        = $qCount('DP');
$menungguTermin40  = $qCount('Termin');     // MOS 40% kamu mapping ke TERMIN
$menungguPelunasan = $qCount('Pelunasan');

// (opsional) kalau mau filter sesuai user/role, contoh untuk PM hanya proyek yang dia PIC site:
// $user = $_SESSION['user']['id'] ?? null;
// if ($user && ($_SESSION['user']['role_id'] ?? '') === 'RL002') {
//   $totalProyek    = (int)$pdo->query("SELECT COUNT(*) FROM proyek WHERE karyawan_id_pic_site=".$pdo->quote($user))->fetchColumn();
//   $proyekBerjalan = (int)$pdo->query("SELECT COUNT(*) FROM proyek WHERE status='Berjalan' AND karyawan_id_pic_site=".$pdo->quote($user))->fetchColumn();
//   $proyekSelesai  = (int)$pdo->query("SELECT COUNT(*) FROM proyek WHERE status='Selesai' AND karyawan_id_pic_site=".$pdo->quote($user))->fetchColumn();
//   // dan pembayaran bisa di-join ke proyek + filter karyawan_id_pic_site = $user
// }
?>

<div class="page-content-wrapper">
    <!-- start page content-->
    <div class="page-content">
        <?php include_once __DIR__ . '/../partials/alert.php'; ?>

        <div class="row row-cols-1 row-cols-lg-2 row-cols-xxl-4">
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body ps-4">
                        <div class="d-flex align-items-start gap-2">
                            <div>
                                <h3 class="mb-0 fs-6">Total Proyek</h3>
                            </div>
                            <div class="ms-auto widget-icon-small text-white bg-gradient-purple">
                                <ion-icon name="briefcase-outline"></ion-icon>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-1">
                            <div>
                                <h1 class="mb-3"><?= number_format($totalProyek, 0, ',', '.') ?></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10">
                    <div class="card-body ps-4">
                        <div class="d-flex align-items-start gap-2">
                            <div>
                                <h3 class="mb-0 fs-6">Proyek Berjalan</h3>
                            </div>
                            <div class="ms-auto widget-icon-small text-white bg-gradient-warning">
                                <ion-icon name="build-outline"></ion-icon>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-1">
                            <div>
                                <h1 class="mb-3"><?= number_format($proyekBerjalan, 0, ',', '.') ?></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-lg-2 row-cols-xxl-4">
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body ps-4">
                        <div class="d-flex align-items-start gap-2">
                            <div>
                                <h3 class="mb-0 fs-6">Proyek Selesai</h3>
                            </div>
                            <div class="ms-auto widget-icon-small text-white bg-gradient-success">
                                <ion-icon name="checkmark-done-outline"></ion-icon>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-1">
                            <div>
                                <h1 class="mb-3"><?= number_format($proyekSelesai, 0, ',', '.') ?></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10">
                    <div class="card-body ps-4">
                        <div class="d-flex align-items-start gap-2">
                            <div>
                                <h3 class="mb-0 fs-6">Menunggu Pembayaran DP</h3>
                            </div>
                            <div class="ms-auto widget-icon-small text-white bg-gradient-danger">
                                <ion-icon name="hourglass-outline"></ion-icon>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-1">
                            <div>
                                <h1 class="mb-3"><?= number_format($menungguDP, 0, ',', '.') ?></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-lg-2 row-cols-xxl-4">
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body ps-4">
                        <div class="d-flex align-items-start gap-2">
                            <div>
                                <h3 class="mb-0 fs-6">Menunggu Pembayaran MOS 40%</h3>
                            </div>
                            <div class="ms-auto widget-icon-small text-white bg-gradient-danger">
                                <ion-icon name="time-outline"></ion-icon>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-1">
                            <div>
                                <h1 class="mb-3"><?= number_format($menungguTermin40, 0, ',', '.') ?></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10">
                    <div class="card-body ps-4">
                        <div class="d-flex align-items-start gap-2">
                            <div>
                                <h3 class="mb-0 fs-6">Menunggu Pelunasan</h3>
                            </div>
                            <div class="ms-auto widget-icon-small text-white bg-gradient-danger">
                                <ion-icon name="alert-circle-outline"></ion-icon>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-1">
                            <div>
                                <h1 class="mb-3"><?= number_format($menungguPelunasan, 0, ',', '.') ?></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--end row-->
    </div>
    <!-- end page content-->
</div>
<!--end page content wrapper-->