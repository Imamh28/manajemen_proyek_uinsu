<?php
// Vars dari controller: $BASE_URL, $pending, $recent
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <?php include __DIR__ . '/../../../partials/alert.php'; ?>

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a></li>
                    <li class="breadcrumb-item active">Persetujuan Tahapan</li>
                </ol>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-3">Pending Approval</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Proyek</th>
                                <th>Tahapan Diusulkan</th>
                                <th>Pengusul</th>
                                <th>Diajukan</th>
                                <th>Catatan Mandor</th>
                                <th>Bukti Foto</th>
                                <th>Bukti Dokumen</th>
                                <th>Aksi</th>
                                <th>Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pending): foreach ($pending as $i => $r): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars($r['proyek_id_proyek'] . ' — ' . $r['nama_proyek']) ?></td>
                                        <td><?= htmlspecialchars($r['requested_tahapan_id'] . ' — ' . $r['nama_tahapan']) ?></td>
                                        <td><?= htmlspecialchars($r['requested_by_name']) ?></td>
                                        <td><?= htmlspecialchars($r['requested_at']) ?></td>
                                        <td><?= htmlspecialchars($r['request_note'] ?? '-') ?></td>
                                        <td>
                                            <a class="btn btn-sm btn-outline-primary"
                                                href="<?= $BASE_URL ?>index.php?r=tahapan-approval/file&id=<?= (int)$r['id'] ?>&kind=foto"
                                                target="_blank">Lihat Foto</a>

                                            <a class="btn btn-sm btn-outline-secondary"
                                                href="<?= $BASE_URL ?>index.php?r=tahapan-approval/file&id=<?= (int)$r['id'] ?>&kind=foto&download=1">Unduh Foto</a>
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-outline-primary"
                                                href="<?= $BASE_URL ?>index.php?r=tahapan-approval/file&id=<?= (int)$r['id'] ?>&kind=dokumen"
                                                target="_blank">Lihat Dokumen</a>

                                            <a class="btn btn-sm btn-outline-secondary"
                                                href="<?= $BASE_URL ?>index.php?r=tahapan-approval/file&id=<?= (int)$r['id'] ?>&kind=dokumen&download=1">Unduh Dokumen</a>
                                        </td>
                                        <td class="d-flex flex-wrap gap-2">
                                            <form method="POST" action="<?= $BASE_URL ?>index.php?r=tahapan-approval/approve" class="d-flex gap-2">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                                <input type="text" name="review_note" class="form-control form-control-sm"
                                                    placeholder="Catatan reviewer (opsional)" maxlength="250" style="width:220px">
                                                <button class="btn btn-sm btn-success">Setujui</button>
                                            </form>

                                            <form method="POST" action="<?= $BASE_URL ?>index.php?r=tahapan-approval/reject" class="d-flex gap-2">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                                <input type="text" name="review_note" class="form-control form-control-sm"
                                                    placeholder="Catatan reviewer (opsional)" maxlength="250" style="width:220px">
                                                <button class="btn btn-sm btn-outline-danger">Tolak</button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST"
                                                action="<?= $BASE_URL ?>index.php?r=tahapan-approval/delete-request"
                                                onsubmit="return confirm('Yakin ingin menghapus request ini? File bukti juga akan ikut dihapus.');">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="hapus_id" value="<?= (int)$r['id'] ?>">
                                                <button class="btn btn-sm btn-outline-dark">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada pengajuan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

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
                                <th>Pengusul</th>
                                <th>Reviewer</th>
                                <th>Catatan Mandor</th>
                                <th>Catatan Reviewer</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent): foreach ($recent as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['requested_at']) ?></td>
                                        <td><?= htmlspecialchars($r['proyek_id_proyek'] . ' — ' . $r['nama_proyek']) ?></td>
                                        <td><?= htmlspecialchars($r['requested_tahapan_id'] . ' — ' . $r['nama_tahapan']) ?></td>
                                        <td><span class="badge <?= $r['status'] === 'approved' ? 'bg-success' : ($r['status'] === 'rejected' ? 'bg-danger' : 'bg-warning') ?>"><?= htmlspecialchars(ucfirst($r['status'])) ?></span></td>
                                        <td><?= htmlspecialchars($r['requested_by_name']) ?></td>
                                        <td><?= htmlspecialchars($r['reviewed_by_name'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($r['request_note'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($r['review_note'] ?? '-') ?></td>
                                        <td>
                                            <form method="POST"
                                                action="<?= $BASE_URL ?>index.php?r=tahapan-approval/delete-request"
                                                onsubmit="return confirm('Yakin ingin menghapus riwayat request ini? File bukti juga akan ikut dihapus.');">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="hapus_id" value="<?= (int)$r['id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">Belum ada riwayat.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>