<?php
// Variabel dari controller: $proyek, $BASE_URL
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
                            <a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Progres Proyek</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="<?= $BASE_URL ?>index.php" method="GET" class="row g-3">
                    <input type="hidden" name="r" value="progres">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control"
                            placeholder="Ketik ID/Nama proyek..."
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
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
                            $status = $p['status'] ?? '';
                            $badge  = 'bg-secondary';
                            if ($status === 'Berjalan')  $badge = 'bg-warning';
                            if ($status === 'Selesai')   $badge = 'bg-success';
                            if ($status === 'Menunggu')  $badge = 'bg-info';
                            if ($status === 'Dibatalkan') $badge = 'bg-danger';
                            $accId = 'acc-' . htmlspecialchars($p['id_proyek']);
                            ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="head-<?= $accId ?>">
                                    <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#<?= $accId ?>"
                                        aria-expanded="false" aria-controls="<?= $accId ?>">
                                        Proyek <?= htmlspecialchars($p['id_proyek']) ?>: <?= htmlspecialchars($p['nama_proyek']) ?>
                                        <span class="badge <?= $badge ?> ms-2"><?= htmlspecialchars($status) ?></span>
                                    </button>
                                </h2>
                                <div id="<?= $accId ?>" class="accordion-collapse collapse" aria-labelledby="head-<?= $accId ?>"
                                    data-bs-parent="#accordionProyekProgress">
                                    <div class="accordion-body">
                                        <ul class="list-group">
                                            <li class="list-group-item"><strong>Deskripsi:</strong> <?= htmlspecialchars($p['deskripsi'] ?? '-') ?></li>
                                            <li class="list-group-item"><strong>Total Biaya:</strong> Rp <?= number_format((float)($p['total_biaya_proyek'] ?? 0), 2, ',', '.') ?></li>
                                            <li class="list-group-item"><strong>Tanggal Mulai:</strong>
                                                <?= !empty($p['tanggal_mulai']) ? date('d M Y', strtotime($p['tanggal_mulai'])) : '-' ?></li>
                                            <li class="list-group-item"><strong>Tanggal Selesai:</strong>
                                                <?= !empty($p['tanggal_selesai']) ? date('d M Y', strtotime($p['tanggal_selesai'])) : 'Belum Selesai' ?></li>
                                            <li class="list-group-item"><strong>Brand:</strong> <?= htmlspecialchars($p['nama_brand'] ?? '-') ?></li>
                                            <li class="list-group-item"><strong>PIC Sales:</strong> <?= htmlspecialchars($p['nama_sales'] ?? '-') ?></li>
                                            <li class="list-group-item"><strong>PIC Site:</strong> <?= htmlspecialchars($p['nama_site'] ?? '-') ?></li>
                                            <li class="list-group-item"><strong>Klien:</strong> <?= htmlspecialchars($p['nama_klien'] ?? '-') ?></li>
                                        </ul>

                                        <h6 class="mt-4 mb-2">Tahapan Proyek</h6>
                                        <?php if (!empty($p['tahapan'])): ?>
                                            <ul class="list-group">
                                                <?php foreach ($p['tahapan'] as $t): ?>
                                                    <li class="list-group-item">
                                                        <strong><?= htmlspecialchars($t['nama_tahapan']) ?></strong><br>
                                                        <small>
                                                            Plan: <?= date('d M Y', strtotime($t['plan_mulai'])) ?>
                                                            s/d <?= date('d M Y', strtotime($t['plan_selesai'])) ?>
                                                            | Aktual Mulai: <?= !empty($t['mulai']) ? date('d M Y', strtotime($t['mulai'])) : '-' ?>
                                                            | Aktual Selesai: <?= !empty($t['selesai']) ? date('d M Y', strtotime($t['selesai'])) : '-' ?>
                                                            | Status: <?= htmlspecialchars($t['status']) ?>
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
                            "<strong><?= htmlspecialchars($_GET['search'] ?? '') ?></strong>".
                            <a href="<?= $BASE_URL ?>index.php?r=progres" class="btn btn-link">Tampilkan semua</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>