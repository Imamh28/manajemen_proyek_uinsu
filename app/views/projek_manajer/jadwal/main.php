<?php
// $BASE_URL, $proyekId, $projek, $jadwal, $projectsDD, $EXISTING_IDS_JSON, $NEXT_TAHAP
$__storeErr = $_SESSION['form_errors']['jadwal_store'] ?? [];
$__storeOld = $_SESSION['form_old']['jadwal_store'] ?? [];
unset($_SESSION['form_errors']['jadwal_store'], $_SESSION['form_old']['jadwal_store']);

$pid = htmlspecialchars($proyekId);
?>
<div class="page-content-wrapper">
    <div class="page-content">

        <?php include __DIR__ . '/../../../partials/alert.php'; ?>

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a></li>
                    <li class="breadcrumb-item active">Jadwal Proyek</li>
                </ol>
            </div>
        </div>

        <!-- Pilih Proyek -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="<?= $BASE_URL ?>index.php" class="row g-2">
                    <input type="hidden" name="r" value="penjadwalan">
                    <div class="col-md-8">
                        <select name="proyek" class="form-select" required>
                            <?php foreach ($projectsDD as $p): ?>
                                <option value="<?= $p['id_proyek'] ?>" <?= ($p['id_proyek'] === ($proyekId ?? '')) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['id_proyek'] . ' — ' . $p['nama_proyek']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100">Tampilkan</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($projek): ?>
            <!-- Tabs ala gambar -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>index.php?r=penjadwalan/detailproyek&proyek=<?= htmlspecialchars($projek['id_proyek']) ?>">Detail Proyek</a></li>
                <li class="nav-item"><a class="nav-link active" href="#">Jadwal Proyek</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>index.php?r=penjadwalan/detailpembayaran&proyek=<?= htmlspecialchars($projek['id_proyek']) ?>">Detail Pembayaran</a></li>
                <!-- opsional: tab dokumen bila ada -->
                <!-- <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>index.php?r=penjadwalan/dokumen&proyek=<?= htmlspecialchars($projek['id_proyek']) ?>">Dokumen Proyek</a></li> -->
            </ul>

            <!-- Header + tombol tambah seperti gambar -->
            <div class="d-flex align-items-center mb-2">
                <h5 class="mb-0">Jadwal Proyek</h5>
                <div class="ms-auto">
                    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addJadwalForm">
                        <ion-icon name="add-circle-outline" class="me-1"></ion-icon> Tambah Jadwal
                    </button>
                </div>
            </div>

            <!-- Info auto-tahapan -->
            <div class="alert alert-info py-2">
                Tahapan akan <b>diisi otomatis</b> berurutan (TH01 → TH06). Tahapan berikutnya: <b><?= htmlspecialchars($NEXT_TAHAP) ?></b>.
            </div>

            <?php
            $projStart = htmlspecialchars($projek['tanggal_mulai'] ?? '');
            $projEnd   = htmlspecialchars($projek['tanggal_selesai'] ?? '');
            ?>

            <!-- FORM TAMBAH: dibuat collapse agar tampil seperti tombol di gambar -->
            <div class="collapse" id="addJadwalForm">
                <div class="card card-body mb-3">
                    <form class="row g-3 live-validate" novalidate method="POST" action="<?= $BASE_URL ?>index.php?r=penjadwalan/store">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="proyek_id_proyek" value="<?= $pid ?>">

                        <div class="col-md-3">
                            <label class="form-label">ID Jadwal</label>
                            <input type="text" name="id_jadwal" required data-unique="id" data-prefix="JDL"
                                class="form-control <?= isset($__storeErr['id_jadwal']) ? 'is-invalid' : '' ?>"
                                placeholder="JDL001" value="<?= htmlspecialchars($__storeOld['id_jadwal'] ?? 'JDL') ?>">
                            <div class="invalid-feedback">
                                <?= $__storeErr['id_jadwal'] ?? 'ID wajib diawali "JDL", setelahnya hanya angka, dan harus unik.' ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Plan Mulai</label>
                            <input type="date" name="plan_mulai" required
                                min="<?= $projStart ?>" max="<?= $projEnd ?>"
                                class="form-control <?= isset($__storeErr['plan_mulai']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($__storeOld['plan_mulai'] ?? '') ?>">
                            <div class="invalid-feedback"><?= $__storeErr['plan_mulai'] ?? 'Tanggal wajib & valid.' ?></div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Plan Selesai</label>
                            <input type="date" name="plan_selesai" required
                                min="<?= $projStart ?>" max="<?= $projEnd ?>"
                                class="form-control <?= isset($__storeErr['plan_selesai']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($__storeOld['plan_selesai'] ?? '') ?>">
                            <div class="invalid-feedback"><?= $__storeErr['plan_selesai'] ?? 'Tanggal wajib & valid.' ?></div>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary w-100">
                                <ion-icon name="add-circle-outline"></ion-icon> Tambah
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- LIST seperti gambar (ada kolom No) -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0 align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width:60px" class="text-center">No</th>
                                    <th>Tahapan</th>
                                    <th>Plan Mulai</th>
                                    <th>Plan Selesai</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th style="width:120px" class="text-center">Durasi (Hari)</th>
                                    <th style="width:140px" class="text-center">Status</th>
                                    <th style="width:110px" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($jadwal)): $no = 1;
                                    foreach ($jadwal as $j): ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($j['nama_tahapan'] ?? $j['daftar_tahapans_id_tahapan']) ?></td>
                                            <td><?= htmlspecialchars($j['plan_mulai']) ?></td>
                                            <td class="text-success"><?= htmlspecialchars($j['plan_selesai']) ?></td>
                                            <td class="text-primary"><?= htmlspecialchars($j['mulai'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($j['selesai'] ?? '-') ?></td>
                                            <td class="text-center"><?= (int)$j['durasi'] ?></td>
                                            <td class="text-center">
                                                <?php
                                                $st = $j['status'];
                                                $cls = ($st === 'Selesai' || $st === 'Sesuai Jadwal') ? 'success'
                                                    : ($st === 'Belum Mulai' ? 'secondary'
                                                        : ($st === 'Terlambat' ? 'danger'
                                                            : 'info')); // Dalam Proses / lainnya => info
                                                ?>
                                                <span class="badge bg-<?= $cls ?>"><?= htmlspecialchars($st) ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex order-actions">
                                                    <a class="ms-3"
                                                        href="<?= $BASE_URL ?>index.php?r=penjadwalan/edit&id=<?= urlencode($j['id_jadwal']) ?>">
                                                        <ion-icon name="create-outline"></ion-icon>
                                                    </a>
                                                    <a class="ms-3" href="javascript:void(0)"
                                                        data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                        data-id="<?= htmlspecialchars($j['id_jadwal']) ?>">
                                                        <ion-icon name="trash-outline"></ion-icon>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Belum ada jadwal.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <a href="<?= $BASE_URL ?>index.php?r=penjadwalan/detailproyek&proyek=<?= htmlspecialchars($projek['id_proyek']) ?>"
                        class="btn btn-secondary mt-3">
                        &laquo; Kembali
                    </a>
                </div>
            </div>

            <!-- Modal Hapus -->
            <div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form class="modal-content" method="POST" action="<?= $BASE_URL ?>index.php?r=penjadwalan/delete">
                        <?= csrf_input(); ?>
                        <div class="modal-header">
                            <h5 class="modal-title">Konfirmasi Hapus</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="hapus_id" id="hapus_id">
                            <input type="hidden" name="hapus_pid" value="<?= $pid ?>">
                            Hapus jadwal ini?
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-danger" type="submit">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                // daftar ID jadwal untuk cek unik
                window.JDL_EXISTING_IDS = <?= $EXISTING_IDS_JSON ?? '[]' ?>;
                // batas tanggal proyek (YYYY-MM-DD)
                const PROJ_START = '<?= $projStart ?>';
                const PROJ_END = '<?= $projEnd ?>';

                (function() {
                    const debounce = (fn, ms = 250) => {
                        let t;
                        return (...a) => {
                            clearTimeout(t);
                            t = setTimeout(() => fn(...a), ms);
                        }
                    };
                    const form = document.querySelector('form.live-validate');
                    if (!form) return;

                    const mulaiEl = form.querySelector('input[name="plan_mulai"]');
                    const selesaiEl = form.querySelector('input[name="plan_selesai"]');
                    if (mulaiEl && selesaiEl) {
                        // set min/max awal
                        mulaiEl.min = PROJ_START;
                        mulaiEl.max = PROJ_END;
                        selesaiEl.min = PROJ_START;
                        selesaiEl.max = PROJ_END;

                        // saat plan_mulai berubah, naikkan min plan_selesai
                        mulaiEl.addEventListener('input', () => {
                            selesaiEl.min = mulaiEl.value || PROJ_START;
                            validate(selesaiEl);
                        });
                    }

                    function setInvalid(el, msg) {
                        el.classList.add('is-invalid');
                        const fb = el.parentElement.querySelector('.invalid-feedback');
                        if (fb && msg) fb.textContent = msg;
                    }

                    function setValid(el) {
                        el.classList.remove('is-invalid');
                    }

                    function validate(el) {
                        const name = el.name;
                        const v = (el.value || '').trim();
                        let msg = '';

                        if (!msg && el.hasAttribute('required') && !v) msg = 'Wajib diisi.';

                        if (!msg && (name === 'plan_mulai' || name === 'plan_selesai') && v && !/^\d{4}-\d{2}-\d{2}$/.test(v))
                            msg = 'Tanggal tidak valid.';

                        // batas terhadap tanggal proyek
                        if (!msg && name === 'plan_mulai' && v) {
                            if (v < PROJ_START) msg = `Plan mulai tidak boleh sebelum ${PROJ_START}.`;
                            else if (v > PROJ_END) msg = `Plan mulai tidak boleh melewati ${PROJ_END}.`;
                        }
                        if (!msg && name === 'plan_selesai' && v) {
                            const minEnd = (mulaiEl?.value || PROJ_START);
                            if (v < minEnd) msg = 'Plan selesai tidak boleh sebelum Plan Mulai.';
                            else if (v > PROJ_END) msg = `Plan selesai tidak boleh melewati ${PROJ_END}.`;
                        }

                        // validasi ID jadwal (tetap)
                        if (!msg && name === 'id_jadwal') {
                            const PFX = (el.dataset.prefix || 'JDL').toUpperCase();
                            const up = v.toUpperCase();
                            if (!up.startsWith(PFX)) msg = `ID harus diawali "${PFX}".`;
                            const suffix = up.slice(PFX.length);
                            if (!msg && /[^0-9]/.test(suffix)) msg = 'Setelah "JDL" hanya angka.';
                            if (!msg) {
                                const list = (window.JDL_EXISTING_IDS || []).map(s => String(s).toUpperCase());
                                if (list.includes(up)) msg = 'ID Jadwal sudah digunakan.';
                            }
                        }

                        if (msg) setInvalid(el, msg);
                        else setValid(el);
                    }

                    // pasang listeners (tetap seperti sebelumnya)
                    form.querySelectorAll('input').forEach(el => {
                        if (!el.parentElement.querySelector('.invalid-feedback')) {
                            const fb = document.createElement('div');
                            fb.className = 'invalid-feedback';
                            el.parentElement.appendChild(fb);
                        }
                        const run = debounce(() => validate(el), 200);
                        el.addEventListener('input', run);
                        el.addEventListener('blur', () => validate(el));
                        el.addEventListener('change', () => validate(el));
                    });

                    form.addEventListener('submit', (e) => {
                        form.querySelectorAll('input').forEach(el => validate(el));
                        if (form.querySelector('.is-invalid')) {
                            e.preventDefault();
                            e.stopPropagation();
                            return;
                        }
                        const btn = form.querySelector('button[type="submit"]');
                        if (btn) {
                            if (btn.dataset.submitting === '1') {
                                e.preventDefault();
                                e.stopPropagation();
                                return;
                            }
                            btn.dataset.submitting = '1';
                            btn.disabled = true;
                            btn.dataset.original = btn.innerHTML;
                            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses…';
                        }
                    });
                })();
            </script>

        <?php else: ?>
            <div class="alert alert-info">Silakan pilih proyek terlebih dahulu.</div>
        <?php endif; ?>

    </div>
</div>