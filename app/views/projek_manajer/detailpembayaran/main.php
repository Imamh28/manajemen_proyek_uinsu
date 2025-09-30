<?php
// From controller: $BASE_URL, $projek, $pembayaran (rows)
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a></li>
                    <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=penjadwalan&proyek=<?= htmlspecialchars($projek['id_proyek'] ?? '') ?>">Jadwal Proyek</a></li>
                    <li class="breadcrumb-item active">Detail Pembayaran</li>
                </ol>
            </div>
        </div>

        <?php if ($projek): ?>
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>index.php?r=penjadwalan/detailproyek&proyek=<?= htmlspecialchars($projek['id_proyek']) ?>">Detail Proyek</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>index.php?r=penjadwalan&proyek=<?= htmlspecialchars($projek['id_proyek']) ?>">Jadwal Proyek</a></li>
                <li class="nav-item"><a class="nav-link active" href="#">Detail Pembayaran</a></li>
            </ul>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Pembayaran untuk Proyek: <?= htmlspecialchars($projek['id_proyek'] . ' — ' . $projek['nama_proyek']) ?></h5>
                    <hr />
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Jenis</th>
                                    <th>Sub Total</th>
                                    <th>Pajak (10%)</th>
                                    <th>Total</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Tanggal Bayar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($pembayaran)): foreach ($pembayaran as $p): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($p['id_pem_bayaran']) ?></td>
                                            <td><?= htmlspecialchars($p['jenis_pembayaran']) ?></td>
                                            <td>Rp <?= number_format((float)$p['sub_total'], 2, ',', '.') ?></td>
                                            <td>Rp <?= number_format((float)$p['pajak_pembayaran'], 2, ',', '.') ?></td>
                                            <td>Rp <?= number_format((float)$p['total_pembayaran'], 2, ',', '.') ?></td>
                                            <td><?= htmlspecialchars($p['tanggal_jatuh_tempo'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($p['tanggal_bayar'] ?? '-') ?></td>
                                            <td><span class="badge <?= $p['status_pembayaran'] === 'Lunas' ? 'bg-success' : 'bg-warning' ?>"><?= htmlspecialchars($p['status_pembayaran']) ?></span></td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Belum ada data pembayaran.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Proyek tidak ditemukan.</div>
        <?php endif; ?>
    </div>
</div>