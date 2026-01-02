<?php
// app/views/projek_manajer/detailproyek.php
// From controller: $BASE_URL, $projek

$esc = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$pid   = $projek['id_proyek'] ?? '';
$stat  = $projek['status'] ?? 'Menunggu';
$badge = ($stat === 'Selesai') ? 'success'
    : (($stat === 'Berjalan') ? 'warning'
        : (($stat === 'Dibatalkan') ? 'danger' : 'info'));

// include alert (robust path)
$alert1 = __DIR__ . '/../../../partials/alert.php';
$alert2 = __DIR__ . '/../../../partials/alert.php';
if (is_file($alert1)) include $alert1;
elseif (is_file($alert2)) include $alert2;
?>
<div class="page-content-wrapper">
    <div class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item">
                        <a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= $BASE_URL ?>index.php?r=penjadwalan&proyek=<?= $esc($pid) ?>">Jadwal Proyek</a>
                    </li>
                    <li class="breadcrumb-item active">Detail Proyek</li>
                </ol>
            </div>
        </div>

        <?php if (!empty($projek)): ?>
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item"><a class="nav-link active" href="javascript:void(0)">Detail Proyek</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $BASE_URL ?>index.php?r=penjadwalan&proyek=<?= $esc($pid) ?>">Jadwal Proyek</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $BASE_URL ?>index.php?r=penjadwalan/detailpembayaran&proyek=<?= $esc($pid) ?>">Detail Pembayaran</a>
                </li>
            </ul>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        Proyek: <?= $esc($projek['id_proyek'] ?? '-') ?> — <?= $esc($projek['nama_proyek'] ?? '-') ?>
                    </h5>
                    <hr />

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Klien:</strong> <?= $esc($projek['nama_klien'] ?? '-') ?></p>
                            <!-- BRAND DIHAPUS TOTAL -->
                            <p><strong>Alamat:</strong> <?= $esc($projek['alamat'] ?? '-') ?></p>
                            <p><strong>Tanggal Mulai:</strong> <?= $esc($projek['tanggal_mulai'] ?? '-') ?></p>
                            <p><strong>Tanggal Selesai:</strong> <?= $esc($projek['tanggal_selesai'] ?? '-') ?></p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>PIC Sales:</strong> <?= $esc($projek['nama_sales'] ?? '-') ?></p>
                            <p><strong>PIC Site:</strong> <?= $esc($projek['nama_site'] ?? '-') ?></p>

                            <p><strong>Status:</strong>
                                <span class="badge bg-<?= $badge ?>"><?= $esc($stat) ?></span>
                            </p>

                            <p><strong>Total Biaya:</strong>
                                Rp <?= number_format((int)($projek['total_biaya_proyek'] ?? 0), 0, ',', '.') ?>
                            </p>

                            <p><strong>Quotation:</strong> <?= $esc($projek['quotation'] ?? '-') ?></p>

                            <?php if (!empty($projek['gambar_kerja'])): ?>
                                <?php
                                // sama gaya dengan view edit kamu: normalisasi rel path
                                $rel = ltrim((string)$projek['gambar_kerja'], '/');
                                $rel = preg_replace('#^public/#', '', $rel);
                                ?>
                                <p class="mb-0"><strong>Gambar Kerja:</strong>
                                    <a href="<?= $BASE_URL . $esc($rel) ?>" target="_blank" rel="noopener">Lihat file</a>
                                </p>
                            <?php else: ?>
                                <p class="mb-0"><strong>Gambar Kerja:</strong> -</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <p class="mt-3 mb-0">
                        <strong>Deskripsi:</strong><br>
                        <?= nl2br($esc($projek['deskripsi'] ?? '-')) ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Proyek tidak ditemukan.</div>
        <?php endif; ?>

    </div>
</div>