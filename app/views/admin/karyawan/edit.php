<?php
// Variabel: $karyawan, $roles, $BASE_URL
// + $EXISTING_EMAILS_JSON, $EXISTING_PHONES_JSON (diinject controller)
$__updErrors = $_SESSION['form_errors']['karyawan_update'] ?? [];
$__updOld    = $_SESSION['form_old']['karyawan_update']    ?? [];
unset($_SESSION['form_errors']['karyawan_update'], $_SESSION['form_old']['karyawan_update']);

$valNama  = $__updOld['nama_karyawan']       ?? $karyawan['nama_karyawan'];
$valTelp  = $__updOld['no_telepon_karyawan'] ?? $karyawan['no_telepon_karyawan'];
$valEmail = $__updOld['email']               ?? $karyawan['email'];
$valRole  = $__updOld['role_id_role']        ?? $karyawan['role_id_role'];
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
                        <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=karyawan">Data Karyawan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Karyawan: <?= htmlspecialchars($karyawan['nama_karyawan']) ?></h5>
                <hr />
                <div class="p-4 border rounded">
                    <form class="row g-3 live-validate" novalidate method="POST" action="<?= $BASE_URL ?>index.php?r=karyawan/update">
                        <?= csrf_input(); ?>
                        <!-- ID tidak bisa diubah -->
                        <input type="hidden" name="id_karyawan" value="<?= htmlspecialchars($karyawan['id_karyawan']) ?>">

                        <div class="col-md-6">
                            <label class="form-label">Nama Karyawan</label>
                            <input type="text" name="nama_karyawan" required data-maxlen="20"
                                class="form-control <?= isset($__updErrors['nama_karyawan']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($valNama) ?>">
                            <div class="invalid-feedback">
                                <?= $__updErrors['nama_karyawan'] ?? 'Nama maksimal 20 karakter.' ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="tel" name="no_telepon_karyawan" required inputmode="numeric" pattern="\d*"
                                data-digits="true" data-maxlen="13" data-unique="phone"
                                data-current-phone="<?= htmlspecialchars($karyawan['no_telepon_karyawan']) ?>"
                                class="form-control <?= isset($__updErrors['no_telepon_karyawan']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($valTelp) ?>">
                            <div class="invalid-feedback">
                                <?= $__updErrors['no_telepon_karyawan'] ?? 'Nomor telepon wajib, hanya angka, maks. 13 digit, dan harus unik.' ?>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" required data-unique="email"
                                data-current-email="<?= htmlspecialchars(strtolower($karyawan['email'])) ?>"
                                class="form-control <?= isset($__updErrors['email']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($valEmail) ?>">
                            <div class="invalid-feedback">
                                <?= $__updErrors['email'] ?? 'Email tidak valid / sudah digunakan.' ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="password" data-minlen="8"
                                class="form-control <?= isset($__updErrors['password']) ? 'is-invalid' : '' ?>"
                                placeholder="Kosongkan jika tidak diubah">
                            <div class="invalid-feedback">
                                <?= $__updErrors['password'] ?? 'Password minimal 8 karakter.' ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select name="role_id_role" required
                                class="form-select <?= isset($__updErrors['role_id_role']) ? 'is-invalid' : '' ?>">
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= htmlspecialchars($r['id_role']) ?>" <?= ($valRole == $r['id_role']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['nama_role']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                <?= $__updErrors['role_id_role'] ?? 'Silakan pilih role.' ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-4">Update Karyawan</button>
                            <a href="<?= $BASE_URL ?>index.php?r=karyawan" class="btn btn-secondary px-4">Batal</a>
                            <button type="button" class="btn btn-outline-secondary ms-2" data-reset-form>Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            // daftar email & telepon untuk validasi (dari controller)
            window.EXISTING_EMAILS = <?= $EXISTING_EMAILS_JSON ?? '[]' ?>;
            window.EXISTING_PHONES = <?= $EXISTING_PHONES_JSON ?? '[]' ?>;

            const debounce = (fn, wait = 250) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...a), wait);
                };
            };

            document.addEventListener('DOMContentLoaded', () => {
                // Anti double submit
                document.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', e => {
                        form.querySelectorAll('input,select,textarea').forEach(el => runValidate(el));
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
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses…';
                    });
                });

                // Reset form
                document.addEventListener('click', e => {
                    const btn = e.target.closest('[data-reset-form]');
                    if (!btn) return;
                    const form = btn.closest('form');
                    if (!form) return;
                    form.reset();
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                });

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

                function runValidate(el) {
                    const val = (el.value || '').trim();
                    let msg = '';

                    if (!msg && el.hasAttribute('required') && !val) msg = 'Field ini wajib diisi.';
                    if (!msg && el.dataset.maxlen) {
                        const mx = parseInt(el.dataset.maxlen, 10);
                        if (val.length > mx) msg = `Maksimal ${mx} karakter.`;
                    }
                    if (!msg && el.dataset.minlen) {
                        const mn = parseInt(el.dataset.minlen, 10);
                        if (val.length > 0 && val.length < mn) msg = `Minimal ${mn} karakter.`;
                    }
                    if (!msg && el.dataset.digits !== undefined && val && !/^\d+$/.test(val)) {
                        msg = 'Nomor telepon hanya boleh angka.';
                    }
                    if (!msg && el.type === 'email' && val && !val.includes('@')) {
                        msg = 'Email harus mengandung "@".';
                    }

                    // Unik email (abaikan current)
                    if (!msg && el.dataset.unique === 'email' && val) {
                        const list = (window.EXISTING_EMAILS || []).map(s => String(s).toLowerCase());
                        const current = (el.dataset.currentEmail || '').toLowerCase();
                        const v = val.toLowerCase();
                        if (v !== current && list.includes(v)) msg = 'Email sudah digunakan.';
                    }

                    // Unik telepon (abaikan current)
                    if (!msg && el.dataset.unique === 'phone' && val) {
                        const norm = String(val).replace(/\D+/g, '');
                        const curr = String(el.dataset.currentPhone || '').replace(/\D+/g, '');
                        const exists = (window.EXISTING_PHONES || []).map(p => String(p).replace(/\D+/g, ''));
                        if (norm && norm !== curr && exists.includes(norm)) msg = 'Nomor telepon sudah digunakan.';
                    }

                    if (msg) setInvalid(el, msg);
                    else setValid(el);
                }

                form.querySelectorAll('input,select,textarea').forEach(el => {
                    const run = debounce(() => runValidate(el), 200);
                    el.addEventListener('input', run);
                    el.addEventListener('change', () => runValidate(el));
                    el.addEventListener('blur', () => runValidate(el));
                });
            });
        </script>

    </div>
</div>