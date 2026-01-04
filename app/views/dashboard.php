<?php
// app/views/dashboard.php

$__DASH_DEBUG__ = (isset($_GET['debug']) && $_GET['debug'] == '1');
if ($__DASH_DEBUG__) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// Pastikan user login (lebih aman, walau router biasanya sudah nge-guard)
require_once __DIR__ . '/../middleware/auth.php';

// Ambil BASE_URL dari init.php kalau ada
if (!isset($BASE_URL) || !is_string($BASE_URL) || trim($BASE_URL) === '') {
    // fallback minimal (kalau suatu saat dipanggil tanpa init)
    $BASE_URL = '/manajemen_proyek_uinsu/app/';
}
$BASE_URL = rtrim($BASE_URL, '/') . '/';

// === ambil PDO ala dashboard lama ===
/** @var PDO|null $pdo */
$pdo = $GLOBALS['pdo'] ?? null;

// fallback kalau global pdo belum diset
if (!$pdo instanceof PDO) {
    require_once __DIR__ . '/../config/database.php'; // menghasilkan $pdo
    if (isset($pdo) && $pdo instanceof PDO) {
        $GLOBALS['pdo'] = $pdo;
    }
}

if (!$pdo instanceof PDO) {
    echo '<div class="alert alert-danger m-3">Koneksi DB tidak tersedia.</div>';
    return;
}

$rupiah = function ($n): string {
    $n = (int)round((float)$n);
    return 'Rp ' . number_format($n, 0, ',', '.');
};

// =====================
// HITUNG METRIK
// =====================
$totalProyek = 0;
$proyekBerjalan = 0;
$proyekSelesai = 0;
$totalRevenue = 0;

$menungguPelunasan = 0;
$pelunasan = 0;

$totalBiayaAll = 0;
$totalPaidAll  = 0;
$totalSisaAll  = 0;

$__dashError = null;

try {
    // 1. Total Proyek
    $totalProyek = (int)$pdo->query("SELECT COUNT(*) FROM proyek")->fetchColumn();

    // 2. Proyek Berjalan
    $st = $pdo->prepare("SELECT COUNT(*) FROM proyek WHERE status = :s");
    $st->execute([':s' => 'Berjalan']);
    $proyekBerjalan = (int)$st->fetchColumn();

    // 3. Proyek Selesai
    $st = $pdo->prepare("SELECT COUNT(*) FROM proyek WHERE status = :s");
    $st->execute([':s' => 'Selesai']);
    $proyekSelesai = (int)$st->fetchColumn();

    // 4. Total Revenue (Σ total_biaya_proyek)
    $totalRevenue = (int)round((float)$pdo->query("SELECT COALESCE(SUM(total_biaya_proyek),0) FROM proyek")->fetchColumn());

    // 5 & 6. Menunggu Pelunasan & Pelunasan berdasarkan Σ pembayaran per proyek
    // Menunggu Pelunasan: paid_total > 0 dan paid_total < total_biaya_proyek
    // Pelunasan: paid_total >= total_biaya_proyek
    $sql = "
        SELECT
            SUM(CASE WHEN x.paid_total > 0 AND x.paid_total < x.total_biaya THEN 1 ELSE 0 END) AS menunggu_pelunasan,
            SUM(CASE WHEN x.total_biaya > 0 AND x.paid_total >= x.total_biaya THEN 1 ELSE 0 END) AS pelunasan,
            COALESCE(SUM(x.total_biaya),0) AS total_biaya_all,
            COALESCE(SUM(x.paid_total),0)  AS total_paid_all
        FROM (
            SELECT
                p.id_proyek,
                COALESCE(p.total_biaya_proyek, 0) AS total_biaya,
                COALESCE(SUM(pb.total_pembayaran), 0) AS paid_total
            FROM proyek p
            LEFT JOIN pembayarans pb ON pb.proyek_id_proyek = p.id_proyek
            GROUP BY p.id_proyek, p.total_biaya_proyek
        ) x
    ";
    $row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC) ?: [];

    $menungguPelunasan = (int)($row['menunggu_pelunasan'] ?? 0);
    $pelunasan         = (int)($row['pelunasan'] ?? 0);

    $totalBiayaAll = (int)round((float)($row['total_biaya_all'] ?? 0));
    $totalPaidAll  = (int)round((float)($row['total_paid_all'] ?? 0));
    $totalSisaAll  = max(0, $totalBiayaAll - $totalPaidAll);
} catch (Throwable $e) {
    $__dashError = $e->getMessage();
}
?>

<div class="page-content-wrapper">
    <div class="page-content">

        <?php include __DIR__ . '/../partials/alert.php'; ?>

        <?php if ($__DASH_DEBUG__): ?>
            <div class="alert alert-info">
                <strong>DEBUG Dashboard:</strong><br>
                File: <?= htmlspecialchars(__FILE__) ?><br>
                DB: <?= htmlspecialchars((string)($pdo->query("SELECT DATABASE()")->fetchColumn() ?? '')) ?><br>
                Role: <?= htmlspecialchars((string)($_SESSION['user']['role_id'] ?? '')) ?>
            </div>
        <?php endif; ?>

        <?php if ($__dashError): ?>
            <div class="alert alert-danger">
                <strong>Dashboard gagal memuat data.</strong><br>
                <?= htmlspecialchars($__dashError) ?><br>
                <?php if (!$__DASH_DEBUG__): ?>
                    <small>Coba buka dengan <code>&debug=1</code> untuk melihat detail.</small>
                <?php endif; ?>
            </div>
        <?php endif; ?>

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
                                <ion-icon name="walk-outline"></ion-icon>
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
                                <h3 class="mb-0 fs-6">Total Revenue</h3>
                            </div>
                            <div class="ms-auto widget-icon-small text-white bg-gradient-info">
                                <ion-icon name="cash-outline"></ion-icon>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-1">
                            <div>
                                <h1 class="mb-3"><?= $rupiah($totalPaidAll) ?></h1>
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
                                <h3 class="mb-0 fs-6">Menunggu Pelunasan</h3>
                            </div>
                            <div class="ms-auto widget-icon-small text-white bg-gradient-danger">
                                <ion-icon name="time-outline"></ion-icon>
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

            <div class="col">
                <div class="card radius-10">
                    <div class="card-body ps-4">
                        <div class="d-flex align-items-start gap-2">
                            <div>
                                <h3 class="mb-0 fs-6">Pelunasan</h3>
                            </div>
                            <div class="ms-auto widget-icon-small text-white bg-gradient-success">
                                <ion-icon name="wallet-outline"></ion-icon>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-1">
                            <div>
                                <h1 class="mb-3"><?= number_format($pelunasan, 0, ',', '.') ?></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>