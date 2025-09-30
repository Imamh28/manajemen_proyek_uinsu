<?php
// Vars: $BASE_URL, $PROJECTS, $CURRENT_PROJECT, $STEPS, $pending, $recent
$pending = $pending ?? [];
$recent  = $recent ?? [];
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <?php include __DIR__ . '/../../../partials/alert.php'; ?>

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a></li>
                    <li class="breadcrumb-item active">Tahapan Proyek (Mandor)</li>
                </ol>
            </div>
        </div>

        <!-- Pilih Proyek -->
        <div class="card mb-3">
            <div class="card-body">
                <form class="row g-3" method="GET" action="<?= $BASE_URL ?>index.php">
                    <input type="hidden" name="r" value="tahapan-aktif">
                    <div class="col-md-8">
                        <label class="form-label">Pilih Proyek</label>
                        <select name="proyek" class="form-select" required>
                            <option value="" disabled <?= $CURRENT_PROJECT ? '' : 'selected' ?>>-- Pilih Proyek --</option>
                            <?php foreach ($PROJECTS as $p): ?>
                                <option value="<?= htmlspecialchars($p['id_proyek']) ?>" <?= ($CURRENT_PROJECT === $p['id_proyek']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['id_proyek'] . ' — ' . $p['nama_proyek']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary">Tampilkan</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($CURRENT_PROJECT): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-2">Tahapan Proyek: <?= htmlspecialchars($CURRENT_PROJECT) ?></h5>

                    <?php if ($STEPS): ?>
                        <form method="POST" action="<?= $BASE_URL ?>index.php?r=tahapan-aktif/update" class="mt-2">
                            <?= csrf_input(); ?>
                            <input type="hidden" name="proyek_id_proyek" value="<?= htmlspecialchars($CURRENT_PROJECT) ?>">

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width:60px">Pilih</th>
                                            <th>Tahapan</th>
                                            <th>Plan Mulai</th>
                                            <th>Plan Selesai</th>
                                            <th>Mulai</th>
                                            <th>Selesai</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($STEPS as $s):
                                            $badge = 'secondary';
                                            if ($s['status'] === 'Sesuai Jadwal') $badge = 'primary';
                                            elseif ($s['status'] === 'Lebih Cepat') $badge = 'success';
                                            elseif ($s['status'] === 'Terlambat')   $badge = 'danger';
                                        ?>
                                            <tr>
                                                <td class="text-center">
                                                    <?php if (!empty($s['_eligible'])): ?>
                                                        <input type="radio" name="id_tahapan" value="<?= htmlspecialchars($s['id_tahapan']) ?>" checked />
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($s['id_tahapan'] . ' — ' . $s['nama_tahapan']) ?></td>
                                                <td><?= htmlspecialchars($s['plan_mulai']) ?></td>
                                                <td><?= htmlspecialchars($s['plan_selesai']) ?></td>
                                                <td><?= htmlspecialchars($s['mulai'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($s['selesai'] ?? '-') ?></td>
                                                <td><span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($s['status']) ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row mt-3 g-2">
                                <div class="col-md-8">
                                    <input type="text" name="catatan" class="form-control" placeholder="Catatan (opsional)">
                                </div>
                                <div class="col-md-4 d-flex gap-2">
                                    <button class="btn btn-success w-100">Ajukan Perubahan Tahapan</button>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">Belum ada jadwal untuk proyek ini.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending milik saya (terfilter proyek) -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">Pengajuan Saya (Pending)</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Proyek</th>
                                    <th>Tahapan Diusulkan</th>
                                    <th>Catatan (Saya)</th>
                                    <th>Diajukan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($pending): foreach ($pending as $i => $r): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= htmlspecialchars($r['proyek_id_proyek'] . ' — ' . $r['nama_proyek']) ?></td>
                                            <td><?= htmlspecialchars($r['requested_tahapan_id'] . ' — ' . $r['nama_tahapan']) ?></td>
                                            <td><?= htmlspecialchars($r['request_note'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($r['requested_at']) ?></td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada pengajuan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Riwayat milik saya (terfilter proyek) -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Riwayat Terakhir</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Proyek</th>
                                    <th>Tahapan</th>
                                    <th>Status</th>
                                    <th>Reviewer</th>
                                    <th>Catatan Saya</th>
                                    <th>Catatan Reviewer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent): foreach ($recent as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['requested_at']) ?></td>
                                            <td><?= htmlspecialchars($r['proyek_id_proyek'] . ' — ' . $r['nama_proyek']) ?></td>
                                            <td><?= htmlspecialchars($r['requested_tahapan_id'] . ' — ' . $r['nama_tahapan']) ?></td>
                                            <td>
                                                <span class="badge <?= $r['status'] === 'approved' ? 'bg-success' : ($r['status'] === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                                    <?= htmlspecialchars(ucfirst($r['status'])) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($r['reviewed_by_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($r['request_note'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($r['review_note'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada riwayat.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>