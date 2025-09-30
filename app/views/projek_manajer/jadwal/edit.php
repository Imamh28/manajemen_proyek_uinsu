<?php
$__updErr = $_SESSION['form_errors']['jadwal_update'] ?? [];
$__updOld = $_SESSION['form_old']['jadwal_update'] ?? [];
unset($_SESSION['form_errors']['jadwal_update'], $_SESSION['form_old']['jadwal_update']);

$val = function (string $k, string $def = '') use ($__updOld, $jadwal) {
    return htmlspecialchars($__updOld[$k] ?? ($jadwal[$k] ?? $def));
};
?>
<div class="page-content-wrapper">
    <div class="page-content">

        <?php include __DIR__ . '/../../../partials/alert.php'; ?>

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a></li>
                    <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=penjadwalan&proyek=<?= htmlspecialchars($jadwal['proyek_id_proyek']) ?>">Jadwal Proyek</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Jadwal: <?= htmlspecialchars($jadwal['id_jadwal']) ?></h5>
                <hr />

                <?php
                $projStart = htmlspecialchars($projek['tanggal_mulai'] ?? '');
                $projEnd   = htmlspecialchars($projek['tanggal_selesai'] ?? '');
                ?>

                <form class="row g-3 live-validate" novalidate method="POST" action="<?= $BASE_URL ?>index.php?r=penjadwalan/update">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="id_jadwal" value="<?= htmlspecialchars($jadwal['id_jadwal']) ?>">
                    <input type="hidden" name="proyek_id_proyek" value="<?= htmlspecialchars($jadwal['proyek_id_proyek']) ?>">

                    <div class="col-md-4">
                        <label class="form-label">Proyek</label>
                        <input type="text" class="form-control"
                            value="<?= htmlspecialchars(($projek['id_proyek'] ?? '') . ' — ' . ($projek['nama_proyek'] ?? '')) ?>" disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tahapan</label>
                        <input type="text" class="form-control"
                            value="<?= htmlspecialchars(($jadwal['daftar_tahapans_id_tahapan'] ?? '') . ' — ' . ($jadwal['nama_tahapan'] ?? '')) ?>" disabled>
                        <small class="text-muted">Tidak dapat diubah (ditetapkan otomatis).</small>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Plan Mulai</label>
                        <input type="date" name="plan_mulai" required
                            min="<?= $projStart ?>" max="<?= $projEnd ?>"
                            class="form-control <?= isset($__updErr['plan_mulai']) ? 'is-invalid' : '' ?>"
                            value="<?= $val('plan_mulai') ?>">
                        <div class="invalid-feedback"><?= $__updErr['plan_mulai'] ?? 'Tanggal wajib & valid.' ?></div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Plan Selesai</label>
                        <input type="date" name="plan_selesai" required
                            min="<?= $projStart ?>" max="<?= $projEnd ?>"
                            class="form-control <?= isset($__updErr['plan_selesai']) ? 'is-invalid' : '' ?>"
                            value="<?= $val('plan_selesai') ?>">
                        <div class="invalid-feedback"><?= $__updErr['plan_selesai'] ?? 'Tanggal wajib & valid.' ?></div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary">Update Jadwal</button>
                        <a href="<?= $BASE_URL ?>index.php?r=penjadwalan&proyek=<?= htmlspecialchars($jadwal['proyek_id_proyek']) ?>" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>

        <script>
            (function() {
                const debounce = (f, m = 250) => {
                    let t;
                    return (...a) => {
                        clearTimeout(t);
                        t = setTimeout(() => f(...a), m)
                    }
                };
                const form = document.querySelector('form.live-validate');
                if (!form) return;

                const PROJ_START = '<?= $projStart ?>';
                const PROJ_END = '<?= $projEnd ?>';

                const mulaiEl = form.querySelector('input[name="plan_mulai"]');
                const selesaiEl = form.querySelector('input[name="plan_selesai"]');

                if (mulaiEl && selesaiEl) {
                    // set batas awal
                    mulaiEl.min = PROJ_START;
                    mulaiEl.max = PROJ_END;
                    selesaiEl.min = mulaiEl.value || PROJ_START;
                    selesaiEl.max = PROJ_END;

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
                    if (!msg && (name === 'plan_mulai' || name === 'plan_selesai') && v && !/^\d{4}-\d{2}-\d{2}$/.test(v)) msg = 'Tanggal tidak valid.';

                    if (!msg && name === 'plan_mulai' && v) {
                        if (v < PROJ_START) msg = `Plan mulai tidak boleh sebelum ${PROJ_START}.`;
                        else if (v > PROJ_END) msg = `Plan mulai tidak boleh melewati ${PROJ_END}.`;
                    }
                    if (!msg && name === 'plan_selesai' && v) {
                        const minEnd = (mulaiEl?.value || PROJ_START);
                        if (v < minEnd) msg = 'Plan selesai tidak boleh sebelum Plan Mulai.';
                        else if (v > PROJ_END) msg = `Plan selesai tidak boleh melewati ${PROJ_END}.`;
                    }

                    if (msg) setInvalid(el, msg);
                    else setValid(el);
                }

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

    </div>
</div>