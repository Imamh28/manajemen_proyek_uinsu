<?php
// Variabel: $tahapan, $BASE_URL
$__updErrors = $_SESSION['form_errors']['tahapan_update'] ?? [];
$__updOld    = $_SESSION['form_old']['tahapan_update']    ?? [];
unset($_SESSION['form_errors']['tahapan_update'], $_SESSION['form_old']['tahapan_update']);

$valNama = $__updOld['nama_tahapan'] ?? $tahapan['nama_tahapan'];
?>
<div class="page-content-wrapper">
    <div class="page-content">

        <?php include __DIR__ . '/../../../partials/alert.php'; ?>

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Data Master</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a></li>
                    <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=tahapan">Daftar Tahapan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Tahapan: <?= htmlspecialchars($tahapan['id_tahapan']) ?></h5>
                <hr />
                <form class="row g-3 live-validate" novalidate method="POST" action="<?= $BASE_URL ?>index.php?r=tahapan/update">
                    <?= csrf_input(); ?>
                    <!-- ID hidden (tidak bisa diubah) -->
                    <input type="hidden" name="id_tahapan" value="<?= htmlspecialchars($tahapan['id_tahapan']) ?>">

                    <div class="col-md-6">
                        <label class="form-label">Nama Tahapan</label>
                        <input type="text" name="nama_tahapan" required data-maxlen="45" data-unique="nama"
                            data-current-name="<?= htmlspecialchars(mb_strtolower($tahapan['nama_tahapan'])) ?>"
                            class="form-control <?= isset($__updErrors['nama_tahapan']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($valNama) ?>">
                        <div class="invalid-feedback">
                            <?= $__updErrors['nama_tahapan'] ?? 'Nama wajib, maksimal 45 karakter, dan unik.' ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary px-4" type="submit">Update Tahapan</button>
                        <a href="<?= $BASE_URL ?>index.php?r=tahapan" class="btn btn-secondary px-4">Batal</a>
                        <button type="button" class="btn btn-outline-secondary ms-2" data-reset-form>Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // daftar nama (lowercase) untuk validasi unik
            window.EXISTING_NAMES = <?= $EXISTING_NAMES_JSON ?? '[]' ?>;

            (function() {
                const debounce = (fn, w = 200) => {
                    let t;
                    return (...a) => {
                        clearTimeout(t);
                        t = setTimeout(() => fn(...a), w);
                    };
                };
                const form = document.querySelector('form.live-validate');
                if (!form) return;

                function setInvalid(el, msg) {
                    el.classList.add('is-invalid');
                    const fb = el.parentElement.querySelector('.invalid-feedback');
                    if (fb && msg) fb.textContent = msg;
                }

                function setValid(el) {
                    el.classList.remove('is-invalid');
                }

                function validate(el) {
                    const v = String(el.value || '').trim();
                    let msg = '';

                    if (el.hasAttribute('required') && !v) msg = 'Nama wajib diisi.';
                    if (!msg && el.dataset.maxlen) {
                        const mx = parseInt(el.dataset.maxlen, 10);
                        if (v.length > mx) msg = `Nama maksimal ${mx} karakter.`;
                    }
                    if (!msg && el.dataset.unique === 'nama') {
                        const cur = (el.dataset.currentName || '').toLowerCase();
                        const vv = v.toLowerCase();
                        const list = (window.EXISTING_NAMES || []);
                        if (vv !== cur && list.includes(vv)) msg = 'Nama tahapan sudah digunakan.';
                    }

                    if (msg) setInvalid(el, msg);
                    else setValid(el);
                }

                const nama = form.querySelector('input[name="nama_tahapan"]');
                if (nama && !nama.parentElement.querySelector('.invalid-feedback')) {
                    const fb = document.createElement('div');
                    fb.className = 'invalid-feedback';
                    nama.parentElement.appendChild(fb);
                }

                const run = debounce(() => validate(nama), 200);
                nama.addEventListener('input', run);
                nama.addEventListener('blur', () => validate(nama));
                nama.addEventListener('change', () => validate(nama));

                form.addEventListener('submit', (e) => {
                    validate(nama);
                    if (form.querySelector('.is-invalid')) {
                        e.preventDefault();
                        e.stopPropagation();
                        return;
                    }
                    const btn = form.querySelector('button[type="submit"]');
                    if (!btn) return;
                    if (btn.dataset.submitting === '1') {
                        e.preventDefault();
                        e.stopPropagation();
                        return;
                    }
                    btn.dataset.submitting = '1';
                    btn.disabled = true;
                    btn.dataset.original = btn.innerHTML;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses…';
                });

                // reset
                form.querySelector('[data-reset-form]')?.addEventListener('click', () => {
                    form.reset();
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                });
            })();
        </script>
    </div>
</div>