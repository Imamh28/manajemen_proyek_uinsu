<?php
$__updErr = $_SESSION['form_errors']['klien_update'] ?? [];
$__updOld = $_SESSION['form_old']['klien_update'] ?? [];
unset($_SESSION['form_errors']['klien_update'], $_SESSION['form_old']['klien_update']);

$valNama   = $__updOld['nama_klien']       ?? $klien['nama_klien'];
$valTelp   = $__updOld['no_telepon_klien'] ?? $klien['no_telepon_klien'];
$valEmail  = $__updOld['email_klien']      ?? $klien['email_klien'];
$valAlamat = $__updOld['alamat_klien']     ?? $klien['alamat_klien'];
?>
<div class="page-content-wrapper">
    <div class="page-content">

        <?php include __DIR__ . '/../../../partials/alert.php'; ?>

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Data Master</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 align-items-center">
                        <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a></li>
                        <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=klien">Data Klien</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Klien: <?= htmlspecialchars($klien['nama_klien']) ?></h5>
                <hr />
                <form class="row g-3 live-validate" novalidate method="POST" action="<?= $BASE_URL ?>index.php?r=klien/update">
                    <?= csrf_input(); ?>
                    <!-- ID tetap hidden (tidak bisa diubah) -->
                    <input type="hidden" name="id_klien" value="<?= htmlspecialchars(strtoupper($klien['id_klien'])) ?>">

                    <div class="col-md-6">
                        <label class="form-label">Nama Klien</label>
                        <input type="text"
                            name="nama_klien"
                            required
                            data-maxlen="40"
                            data-unique="nama"
                            data-current-name="<?= htmlspecialchars(strtolower($klien['nama_klien'])) ?>"
                            class="form-control <?= isset($__updErr['nama_klien']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($valNama) ?>">
                        <div class="invalid-feedback">
                            <?= $__updErr['nama_klien'] ?? 'Wajib/unik; maks. 40 karakter.' ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="tel"
                            name="no_telepon_klien"
                            required
                            inputmode="numeric"
                            pattern="\d*"
                            data-digits="true"
                            maxlength="13"
                            data-current-phone="<?= htmlspecialchars($klien['no_telepon_klien']) ?>"
                            class="form-control <?= isset($__updErr['no_telepon_klien']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($valTelp) ?>">
                        <div class="invalid-feedback">
                            <?= $__updErr['no_telepon_klien'] ?? 'Wajib, angka saja, maks. 13 digit, dan unik.' ?>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Email</label>
                        <input type="email"
                            name="email_klien"
                            required
                            data-unique="email"
                            data-current-email="<?= htmlspecialchars(strtolower($klien['email_klien'])) ?>"
                            class="form-control <?= isset($__updErr['email_klien']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($valEmail) ?>">
                        <div class="invalid-feedback">
                            <?= $__updErr['email_klien'] ?? 'Email wajib/valid/unik.' ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat_klien" rows="3" required class="form-control <?= isset($__updErr['alamat_klien']) ? 'is-invalid' : '' ?>"><?= htmlspecialchars($valAlamat) ?></textarea>
                        <div class="invalid-feedback">
                            <?= $__updErr['alamat_klien'] ?? 'Alamat wajib diisi.' ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary px-4" type="submit">Update Klien</button>
                        <a href="<?= $BASE_URL ?>index.php?r=klien" class="btn btn-secondary px-4">Batal</a>
                        <button type="button" class="btn btn-outline-secondary ms-2" data-reset-form>Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // data existing utk validasi unik di halaman edit
            window.KLIEN_EXISTING_EMAILS = <?= $EXISTING_EMAILS_JSON ?? '[]' ?>;
            window.KLIEN_EXISTING_NAMES = <?= $EXISTING_NAMES_JSON  ?? '[]' ?>;
            window.KLIEN_EXISTING_PHONES = <?= $EXISTING_PHONES_JSON ?? '[]' ?>;

            (function() {
                const debounce = (fn, w = 250) => {
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

                const existingEmails = (window.KLIEN_EXISTING_EMAILS || []).map(s => String(s).toLowerCase());
                const existingNames = (window.KLIEN_EXISTING_NAMES || []).map(s => String(s).toLowerCase());
                const existingPhones = (window.KLIEN_EXISTING_PHONES || []).map(s => String(s));

                function validate(el) {
                    const name = el.name;
                    const raw = String(el.value || '');
                    const v = raw.trim();
                    let msg = '';

                    if (!el.parentElement.querySelector('.invalid-feedback')) {
                        const fb = document.createElement('div');
                        fb.className = 'invalid-feedback';
                        el.parentElement.appendChild(fb);
                    }

                    // required
                    if (!msg && el.hasAttribute('required') && !v) msg = 'Field ini wajib diisi.';

                    // maxlen (nama)
                    if (!msg && el.dataset.maxlen) {
                        const mx = parseInt(el.dataset.maxlen, 10);
                        if (v.length > mx) msg = `Maksimal ${mx} karakter.`;
                    }

                    // telepon: hanya angka, max 13, unik (abaikan current)
                    if (!msg && name === 'no_telepon_klien') {
                        if (!/^\d+$/.test(v)) msg = 'Nomor telepon hanya boleh angka.';
                        else if (v.length > 13) msg = 'Nomor telepon maksimal 13 digit.';
                        else {
                            const cur = String(el.dataset.currentPhone || '');
                            if (v !== cur && existingPhones.includes(v)) msg = 'Nomor telepon sudah digunakan.';
                        }
                    }

                    // email: ada @, unik (abaikan current)
                    if (!msg && el.type === 'email') {
                        if (!v.includes('@')) msg = 'Email harus mengandung "@".';
                        else {
                            const cur = String(el.dataset.currentEmail || '').toLowerCase();
                            if (v.toLowerCase() !== cur && existingEmails.includes(v.toLowerCase())) {
                                msg = 'Email sudah digunakan.';
                            }
                        }
                    }

                    // nama: unik (abaikan current)
                    if (!msg && el.dataset.unique === 'nama') {
                        const cur = String(el.dataset.currentName || '').toLowerCase();
                        if (v.toLowerCase() !== cur && existingNames.includes(v.toLowerCase())) {
                            msg = 'Nama klien sudah digunakan.';
                        }
                    }

                    if (msg) setInvalid(el, msg);
                    else setValid(el);
                }

                // pasang listener
                form.querySelectorAll('input,textarea,select').forEach(el => {
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

                // submit guard + spinner
                form.addEventListener('submit', (e) => {
                    form.querySelectorAll('input,textarea,select').forEach(validate);
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