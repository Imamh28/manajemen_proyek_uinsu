<?php
// app/views/admin/progres/main.php
// Variabel dari controller: $proyek (array), $BASE_URL (string)

$esc = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

/**
 * HTML id attribute tidak boleh pakai htmlspecialchars hasilnya (bisa jadi ada &quot; dll).
 * Jadi kita "sanitize" ke karakter aman.
 */
$safe_id = function ($v) {
    $v = (string)$v;
    $v = preg_replace('/[^A-Za-z0-9_-]/', '-', $v);
    return trim($v, '-');
};

$fmt_date = function ($v, $fallback = '-') {
    $v = trim((string)$v);
    if ($v === '') return $fallback;
    $ts = strtotime($v);
    if ($ts === false) return $fallback;
    return date('d M Y', $ts);
};
?>
<div class="page-content-wrapper">
    <div class="page-content">

        <?php include __DIR__ . '/../../../partials/alert.php'; ?>

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 align-items-center">
                        <li class="breadcrumb-item">
                            <a href="<?= $esc($BASE_URL) ?>index.php?r=dashboard">
                                <ion-icon name="home-outline"></ion-icon>
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Progres Proyek</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="<?= $esc($BASE_URL) ?>index.php" method="GET" class="row g-3">
                    <input type="hidden" name="r" value="progres">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control"
                            placeholder="Ketik ID/Nama proyek..."
                            value="<?= $esc($_GET['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit">Cari</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="accordion" id="accordionProyekProgress">
                    <?php if (!empty($proyek)): ?>
                        <?php foreach ($proyek as $p): ?>
                            <?php
                            $status = (string)($p['status'] ?? '');
                            $badge  = 'bg-secondary';
                            if ($status === 'Berjalan')   $badge = 'bg-warning';
                            if ($status === 'Selesai')    $badge = 'bg-success';
                            if ($status === 'Menunggu')   $badge = 'bg-info';
                            if ($status === 'Dibatalkan') $badge = 'bg-danger';

                            $pidSafe = $safe_id($p['id_proyek'] ?? '');
                            $accId   = 'acc-' . ($pidSafe !== '' ? $pidSafe : uniqid());
                            ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="head-<?= $esc($accId) ?>">
                                    <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#<?= $esc($accId) ?>"
                                        aria-expanded="false" aria-controls="<?= $esc($accId) ?>">
                                        Proyek <?= $esc($p['id_proyek'] ?? '-') ?>: <?= $esc($p['nama_proyek'] ?? '-') ?>
                                        <span class="badge <?= $esc($badge) ?> ms-2"><?= $esc($status ?: '-') ?></span>
                                    </button>
                                </h2>

                                <div id="<?= $esc($accId) ?>" class="accordion-collapse collapse"
                                    aria-labelledby="head-<?= $esc($accId) ?>"
                                    data-bs-parent="#accordionProyekProgress">
                                    <div class="accordion-body">
                                        <ul class="list-group">
                                            <li class="list-group-item">
                                                <strong>Deskripsi:</strong> <?= $esc($p['deskripsi'] ?? '-') ?>
                                            </li>
                                            <li class="list-group-item">
                                                <strong>Total Biaya:</strong>
                                                Rp <?= number_format((float)($p['total_biaya_proyek'] ?? 0), 0, ',', '.') ?>
                                            </li>
                                            <li class="list-group-item">
                                                <strong>Tanggal Mulai:</strong> <?= $esc($fmt_date($p['tanggal_mulai'] ?? '')) ?>
                                            </li>
                                            <li class="list-group-item">
                                                <strong>Tanggal Selesai:</strong>
                                                <?= $esc(!empty($p['tanggal_selesai']) ? $fmt_date($p['tanggal_selesai']) : 'Belum Selesai') ?>
                                            </li>

                                            <!-- BRAND DIHAPUS TOTAL -->
                                            <li class="list-group-item">
                                                <strong>PIC Sales:</strong> <?= $esc($p['nama_sales'] ?? '-') ?>
                                            </li>
                                            <li class="list-group-item">
                                                <strong>PIC Site:</strong> <?= $esc($p['nama_site'] ?? '-') ?>
                                            </li>
                                            <li class="list-group-item">
                                                <strong>Klien:</strong> <?= $esc($p['nama_klien'] ?? '-') ?>
                                            </li>
                                        </ul>

                                        <h6 class="mt-4 mb-2">Tahapan Proyek</h6>
                                        <?php if (!empty($p['tahapan']) && is_array($p['tahapan'])): ?>
                                            <ul class="list-group">
                                                <?php foreach ($p['tahapan'] as $t): ?>
                                                    <?php
                                                    $planMulai   = $fmt_date($t['plan_mulai'] ?? '', '-');
                                                    $planSelesai = $fmt_date($t['plan_selesai'] ?? '', '-');
                                                    $aktMulai    = $fmt_date($t['mulai'] ?? '', '-');
                                                    $aktSelesai  = $fmt_date($t['selesai'] ?? '', '-');
                                                    ?>
                                                    <li class="list-group-item">
                                                        <strong><?= $esc($t['nama_tahapan'] ?? '-') ?></strong><br>
                                                        <small>
                                                            Plan: <?= $esc($planMulai) ?> s/d <?= $esc($planSelesai) ?>
                                                            | Aktual Mulai: <?= $esc($aktMulai) ?>
                                                            | Aktual Selesai: <?= $esc($aktSelesai) ?>
                                                            | Status: <?= $esc($t['status'] ?? '-') ?>
                                                        </small>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <div class="alert alert-light border mt-2 mb-0">
                                                Belum ada tahapan dijadwalkan.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-warning text-center mb-0">
                            Tidak ada data proyek untuk kata kunci
                            "<strong><?= $esc($_GET['search'] ?? '') ?></strong>".
                            <a href="<?= $esc($BASE_URL) ?>index.php?r=progres" class="btn btn-link">Tampilkan semua</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>