<?php
// app/views/mandor/tahapan-aktif/main.php
// Vars: $BASE_URL, $PROJECTS, $CURRENT_PROJECT, $STEPS, $pending, $recent

$pending = $pending ?? [];
$recent  = $recent ?? [];
$PROJECTS = $PROJECTS ?? [];
$STEPS    = $STEPS ?? [];
$CURRENT_PROJECT = $CURRENT_PROJECT ?? null;

// Helper escape
if (!function_exists('e')) {
    function e($v)
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

// Optional: ambil old input + errors jika kamu pakai session flash
$__old = $_SESSION['form_old']['tahapan_aktif_update'] ?? [];
$__err = $_SESSION['form_errors']['tahapan_aktif_update'] ?? [];
unset($_SESSION['form_old']['tahapan_aktif_update'], $_SESSION['form_errors']['tahapan_aktif_update']);

$selectedTahapan = $selectedTahapan ?? ($__old['id_tahapan'] ?? null);
$catatanOld      = $catatanOld ?? ($__old['catatan'] ?? '');

// Karena mandor hanya punya 1 proyek, kita ambil proyek itu saja
$projectInfo = null;
if (!empty($PROJECTS) && count($PROJECTS) >= 1) {
    // biasanya hanya 1
    $projectInfo = $PROJECTS[0];
}

// Tentukan current project id
if (!$CURRENT_PROJECT && $projectInfo) {
    $CURRENT_PROJECT = $projectInfo['id_proyek'] ?? null;
}

// Cek apakah ada tahapan eligible
$hasEligible = false;
if (!empty($STEPS)) {
    foreach ($STEPS as $s) {
        if (!empty($s['_eligible'])) {
            $hasEligible = true;
            break;
        }
    }
}
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <?php include __DIR__ . '/../../../partials/alert.php'; ?>

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item">
                        <a href="<?= e($BASE_URL) ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active">Tahapan Proyek (Mandor)</li>
                </ol>
            </div>
        </div>

        <!-- Info Proyek Mandor (tanpa select) -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-1">Proyek Anda</h5>

                <?php if (!$projectInfo && !$CURRENT_PROJECT): ?>
                    <div class="alert alert-warning mb-0">
                        Proyek mandor tidak ditemukan / belum ditetapkan.
                    </div>
                <?php else: ?>
                    <?php
                    $pid  = $projectInfo['id_proyek'] ?? $CURRENT_PROJECT;
                    $pnam = $projectInfo['nama_proyek'] ?? '';
                    ?>
                    <div class="d-flex flex-column">
                        <div class="fw-semibold">
                            <?= e($pid . ($pnam ? ' — ' . $pnam : '')) ?>
                        </div>
                        <small class="text-muted">Tahapan aktif & pengajuan perubahan tahapan untuk proyek ini.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($CURRENT_PROJECT): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-2">Tahapan Proyek: <?= e($CURRENT_PROJECT) ?></h5>

                    <?php if (!empty($STEPS)): ?>
                        <form method="POST" action="<?= e($BASE_URL) ?>index.php?r=tahapan-aktif/update" class="mt-2">
                            <?= csrf_input(); ?>
                            <input type="hidden" name="proyek_id_proyek" value="<?= e($CURRENT_PROJECT) ?>">

                            <?php if (!empty($__err)): ?>
                                <div class="alert alert-danger">
                                    <div class="fw-bold mb-1">Terjadi kesalahan:</div>
                                    <ul class="mb-0">
                                        <?php foreach ($__err as $msg): ?>
                                            <li><?= e($msg) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!$hasEligible): ?>
                                <div class="alert alert-warning">
                                    Tidak ada tahapan yang bisa diajukan saat ini (tidak ada tahapan eligible).
                                </div>
                            <?php endif; ?>

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
                                        <?php $defaultCheckedDone = false; // FIX: hanya satu radio yang checked 
                                        ?>
                                        <?php foreach ($STEPS as $s): ?>
                                            <?php
                                            $status = $s['status'] ?? '—';
                                            $badge = 'secondary';
                                            if ($status === 'Sesuai Jadwal') $badge = 'primary';
                                            elseif ($status === 'Lebih Cepat') $badge = 'success';
                                            elseif ($status === 'Terlambat')   $badge = 'danger';

                                            $eligible    = !empty($s['_eligible']);
                                            $idTahapan   = $s['id_tahapan'] ?? '';
                                            $namaTahapan = $s['nama_tahapan'] ?? '';

                                            // Checked logic
                                            $isChecked = false;
                                            if ($eligible) {
                                                if ($selectedTahapan !== null && $selectedTahapan !== '') {
                                                    $isChecked = ((string)$selectedTahapan === (string)$idTahapan);
                                                } else {
                                                    if (!$defaultCheckedDone) {
                                                        $isChecked = true;
                                                        $defaultCheckedDone = true;
                                                    }
                                                }
                                            }

                                            $planMulai   = $s['plan_mulai'] ?? '-';
                                            $planSelesai = $s['plan_selesai'] ?? '-';
                                            $mulai       = $s['mulai'] ?? '-';
                                            $selesai     = $s['selesai'] ?? '-';
                                            ?>
                                            <tr>
                                                <td class="text-center">
                                                    <?php if ($eligible): ?>
                                                        <input
                                                            type="radio"
                                                            name="id_tahapan"
                                                            value="<?= e($idTahapan) ?>"
                                                            <?= $isChecked ? 'checked' : '' ?>
                                                            <?= $hasEligible ? 'required' : '' ?> />
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= e($idTahapan . ' — ' . $namaTahapan) ?></td>
                                                <td><?= e($planMulai) ?></td>
                                                <td><?= e($planSelesai) ?></td>
                                                <td><?= e($mulai) ?></td>
                                                <td><?= e($selesai) ?></td>
                                                <td><span class="badge bg-<?= e($badge) ?>"><?= e($status) ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row mt-3 g-2">
                                <div class="col-md-8">
                                    <input
                                        type="text"
                                        name="catatan"
                                        class="form-control"
                                        placeholder="Catatan (opsional)"
                                        value="<?= e($catatanOld) ?>"
                                        <?= $hasEligible ? '' : 'disabled' ?>>
                                </div>
                                <div class="col-md-4 d-flex gap-2">
                                    <button class="btn btn-success w-100" <?= $hasEligible ? '' : 'disabled' ?>>
                                        Ajukan Perubahan Tahapan
                                    </button>
                                </div>
                            </div>

                            <div class="mt-2">
                                <small class="text-muted">
                                    * Hanya tahapan yang memenuhi kriteria (eligible) yang bisa dipilih.
                                </small>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">Belum ada jadwal untuk proyek ini.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending milik saya -->
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
                                <?php if (!empty($pending)): ?>
                                    <?php foreach ($pending as $i => $r): ?>
                                        <tr>
                                            <td><?= (int)$i + 1 ?></td>
                                            <td><?= e(($r['proyek_id_proyek'] ?? '-') . ' — ' . ($r['nama_proyek'] ?? '-')) ?></td>
                                            <td><?= e(($r['requested_tahapan_id'] ?? '-') . ' — ' . ($r['nama_tahapan'] ?? '-')) ?></td>
                                            <td><?= e($r['request_note'] ?? '-') ?></td>
                                            <td><?= e($r['requested_at'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada pengajuan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Riwayat -->
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
                                <?php if (!empty($recent)): ?>
                                    <?php foreach ($recent as $r): ?>
                                        <?php
                                        $st = $r['status'] ?? 'pending';
                                        $badge = ($st === 'approved') ? 'bg-success' : (($st === 'rejected') ? 'bg-danger' : 'bg-warning');
                                        ?>
                                        <tr>
                                            <td><?= e($r['requested_at'] ?? '-') ?></td>
                                            <td><?= e(($r['proyek_id_proyek'] ?? '-') . ' — ' . ($r['nama_proyek'] ?? '-')) ?></td>
                                            <td><?= e(($r['requested_tahapan_id'] ?? '-') . ' — ' . ($r['nama_tahapan'] ?? '-')) ?></td>
                                            <td>
                                                <span class="badge <?= e($badge) ?>">
                                                    <?= e(ucfirst($st)) ?>
                                                </span>
                                            </td>
                                            <td><?= e($r['reviewed_by_name'] ?? '-') ?></td>
                                            <td><?= e($r['request_note'] ?? '-') ?></td>
                                            <td><?= e($r['review_note'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
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