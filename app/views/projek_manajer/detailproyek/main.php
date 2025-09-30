<?php
// From controller: $BASE_URL, $projek
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a></li>
                    <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=penjadwalan&proyek=<?= htmlspecialchars($projek['id_proyek'] ?? '') ?>">Jadwal Proyek</a></li>
                    <li class="breadcrumb-item active">Detail Proyek</li>
                </ol>
            </div>
        </div>

        <?php if ($projek): ?>
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item"><a class="nav-link active" href="#">Detail Proyek</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>index.php?r=penjadwalan&proyek=<?= htmlspecialchars($projek['id_proyek']) ?>">Jadwal Proyek</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>index.php?r=penjadwalan/detailpembayaran&proyek=<?= htmlspecialchars($projek['id_proyek']) ?>">Detail Pembayaran</a></li>
            </ul>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Proyek: <?= htmlspecialchars($projek['id_proyek']) ?> — <?= htmlspecialchars($projek['nama_proyek']) ?></h5>
                    <hr />
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Klien:</strong> <?= htmlspecialchars($projek['nama_klien'] ?? '-') ?></p>
                            <p><strong>Brand:</strong> <?= htmlspecialchars($projek['nama_brand'] ?? '-') ?></p>
                            <p><strong>Alamat:</strong> <?= htmlspecialchars($projek['alamat'] ?? '-') ?></p>
                            <p><strong>Tanggal Mulai:</strong> <?= htmlspecialchars($projek['tanggal_mulai'] ?? '-') ?></p>
                            <p><strong>Tanggal Selesai:</strong> <?= htmlspecialchars($projek['tanggal_selesai'] ?? '-') ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>PIC Sales:</strong> <?= htmlspecialchars($projek['nama_sales'] ?? '-') ?></p>
                            <p><strong>PIC Site:</strong> <?= htmlspecialchars($projek['nama_site'] ?? '-') ?></p>
                            <p><strong>Status:</strong> <span class="badge bg-<?= $projek['status'] === 'Selesai' ? 'success' : ($projek['status'] === 'Berjalan' ? 'warning' : ($projek['status'] === 'Dibatalkan' ? 'danger' : 'info')) ?>"><?= htmlspecialchars($projek['status']) ?></span></p>
                            <p><strong>Total Biaya:</strong> Rp <?= number_format((float)$projek['total_biaya_proyek'], 2, ',', '.') ?></p>
                            <p><strong>Quotation:</strong> <?= htmlspecialchars($projek['quotation'] ?? '-') ?></p>
                        </div>
                    </div>
                    <p class="mt-2"><strong>Deskripsi:</strong><br><?= nl2br(htmlspecialchars($projek['deskripsi'] ?? '-')) ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Proyek tidak ditemukan.</div>
        <?php endif; ?>
    </div>
</div>