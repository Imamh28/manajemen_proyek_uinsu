<?php
// var untuk error/old dari sesi
$__storeErr = $_SESSION['form_errors']['klien_store'] ?? [];
$__storeOld = $_SESSION['form_old']['klien_store'] ?? [];
unset($_SESSION['form_errors']['klien_store'], $_SESSION['form_old']['klien_store']);
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
                        <li class="breadcrumb-item active">Data Klien</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- FORM -->
        <form class="row g-3 live-validate" novalidate action="<?= $BASE_URL ?>index.php?r=klien/store" method="POST">
            <?= csrf_input(); ?>

            <div class="col-md-6">
                <label class="form-label">ID Klien</label>
                <input type="text"
                    name="id_klien"
                    required
                    data-unique="id"
                    data-prefix="KL"
                    class="form-control <?= isset($__storeErr['id_klien']) ? 'is-invalid' : '' ?>"
                    placeholder="KL001"
                    value="<?= htmlspecialchars($__storeOld['id_klien'] ?? 'KL') ?>">
                <div class="invalid-feedback">
                    <?= $__storeErr['id_klien'] ?? 'Wajib diawali "KL", hanya angka setelahnya, dan unik.' ?>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nama Klien</label>
                <input type="text"
                    name="nama_klien"
                    required
                    data-maxlen="40"
                    data-unique="nama"
                    class="form-control <?= isset($__storeErr['nama_klien']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['nama_klien'] ?? '') ?>">
                <div class="invalid-feedback">
                    <?= $__storeErr['nama_klien'] ?? 'Wajib, unik, maksimal 40 karakter.' ?>
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
                    data-maxlen="13"
                    class="form-control <?= isset($__storeErr['no_telepon_klien']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['no_telepon_klien'] ?? '') ?>">
                <div class="invalid-feedback">
                    <?= $__storeErr['no_telepon_klien'] ?? 'Wajib, angka saja, maks. 13 digit, dan unik.' ?>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email"
                    name="email_klien"
                    required
                    data-unique="email"
                    class="form-control <?= isset($__storeErr['email_klien']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['email_klien'] ?? '') ?>">
                <div class="invalid-feedback">
                    <?= $__storeErr['email_klien'] ?? 'Wajib, harus mengandung "@", dan unik.' ?>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">Alamat</label>
                <textarea name="alamat_klien" rows="3" required class="form-control <?= isset($__storeErr['alamat_klien']) ? 'is-invalid' : '' ?>"><?= htmlspecialchars($__storeOld['alamat_klien'] ?? '') ?></textarea>
                <div class="invalid-feedback">
                    <?= $__storeErr['alamat_klien'] ?? 'Alamat wajib diisi.' ?>
                </div>
            </div>

            <div class="col-12 mb-4">
                <button type="submit" class="btn btn-primary px-4">Tambah Klien</button>
                <button type="button" class="btn btn-outline-secondary ms-2" data-reset-form>Reset</button>
            </div>
        </form>

        <!-- LIST -->
        <div class="card mt-2">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0">Daftar Klien</h5>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <form action="<?= $BASE_URL ?>index.php" method="GET" class="d-flex">
                            <input type="hidden" name="r" value="klien">
                            <input type="text" class="form-control" name="search" placeholder="Cari klien..."
                                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width:250px">
                            <button class="btn btn-primary ms-2">Cari</button>
                        </form>
                        <a class="btn btn-outline-primary"
                            href="<?= $BASE_URL ?>index.php?r=klien/export&search=<?= urlencode($_GET['search'] ?? '') ?>">
                            Export CSV
                        </a>
                    </div>
                </div>
                <hr />
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Telepon</th>
                                <th>Email</th>
                                <th>Alamat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($kliens)): foreach ($kliens as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['id_klien']) ?></td>
                                        <td><?= htmlspecialchars($r['nama_klien']) ?></td>
                                        <td><?= htmlspecialchars($r['no_telepon_klien']) ?></td>
                                        <td><?= htmlspecialchars($r['email_klien']) ?></td>
                                        <td><?= htmlspecialchars($r['alamat_klien']) ?></td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <a class="ms-3" href="<?= $BASE_URL ?>index.php?r=klien/edit&id=<?= urlencode($r['id_klien']) ?>">
                                                    <ion-icon name="create-outline"></ion-icon>
                                                </a>
                                                <a class="ms-3" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalHapus" data-id="<?= htmlspecialchars($r['id_klien']) ?>">
                                                    <ion-icon name="trash-outline"></ion-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada data.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Hapus -->
        <div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="<?= $BASE_URL ?>index.php?r=klien/delete">
                    <?= csrf_input(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="hapus_id" id="hapus_id">
                        Hapus klien ini?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // inject data unik
            window.KLIEN_EXISTING_IDS = <?= $EXISTING_IDS_JSON    ?? '[]' ?>;
            window.KLIEN_EXISTING_EMAILS = <?= $EXISTING_EMAILS_JSON ?? '[]' ?>;
            window.KLIEN_EXISTING_NAMES = <?= $EXISTING_NAMES_JSON  ?? '[]' ?>;
            window.KLIEN_EXISTING_PHONES = <?= $EXISTING_PHONES_JSON ?? '[]' ?>;

            (function() {
                // modal hapus
                const modalEl = document.getElementById('modalHapus');
                if (modalEl) {
                    modalEl.addEventListener('show.bs.modal', (ev) => {
                        const id = ev.relatedTarget?.getAttribute('data-id') || '';
                        const input = document.getElementById('hapus_id');
                        if (input) input.value = id;
                    });
                }

                const form = document.querySelector('form.live-validate');
                if (!form) return;

                const debounce = (fn, w = 250) => {
                    let t;
                    return (...a) => {
                        clearTimeout(t);
                        t = setTimeout(() => fn(...a), w);
                    };
                };

                function setInvalid(el, msg) {
                    el.classList.add('is-invalid');
                    const fb = el.parentElement.querySelector('.invalid-feedback');
                    if (fb && msg) fb.textContent = msg;
                }

                function setValid(el) {
                    el.classList.remove('is-invalid');
                }

                // ====== Kunci prefix "KL" + hanya digit setelahnya ======
                const idInput = form.querySelector('input[name="id_klien"][data-prefix]');
                if (idInput) {
                    const PFX = (idInput.dataset.prefix || 'KL').toUpperCase();
                    const NAV = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'];

                    // Set default
                    if (!idInput.value || !idInput.value.toUpperCase().startsWith(PFX)) idInput.value = PFX;

                    idInput.addEventListener('keydown', (ev) => {
                        const start = idInput.selectionStart ?? 0;
                        const end = idInput.selectionEnd ?? start;

                        // allow navigation / modifiers
                        if (NAV.includes(ev.key) || ev.ctrlKey || ev.metaKey || ev.altKey) return;

                        // protect prefix area
                        if (start < PFX.length) {
                            if (ev.key === 'Backspace' || ev.key === 'Delete') {
                                ev.preventDefault();
                                return;
                            }
                            if (ev.key.length === 1) {
                                // move caret to end of prefix
                                idInput.setSelectionRange(PFX.length, PFX.length);
                            }
                        }
                        // only digits allowed as characters
                        if (ev.key.length === 1 && !/^\d$/.test(ev.key)) ev.preventDefault();
                    });

                    idInput.addEventListener('input', () => {
                        let v = (idInput.value || '').toUpperCase();
                        if (!v.startsWith(PFX)) v = PFX + v.replace(/^KL/i, '');
                        const digits = v.slice(PFX.length).replace(/\D+/g, '');
                        idInput.value = PFX + digits;
                        validate(idInput);
                    });

                    idInput.addEventListener('paste', (ev) => {
                        ev.preventDefault();
                        const text = (ev.clipboardData || window.clipboardData).getData('text') || '';
                        const digits = text.replace(/^KL/i, '').replace(/\D+/g, '');

                        const start = idInput.selectionStart ?? idInput.value.length;
                        const end = idInput.selectionEnd ?? start;

                        const suffix = idInput.value.toUpperCase().slice(PFX.length);
                        const before = suffix.slice(0, Math.max(0, start - PFX.length));
                        const after = suffix.slice(Math.max(0, end - PFX.length));

                        const merged = (before + digits + after).replace(/\D+/g, '');
                        idInput.value = PFX + merged;

                        const caret = PFX.length + before.length + digits.length;
                        idInput.setSelectionRange(caret, caret);
                        validate(idInput);
                    });
                }
                // =======================================================

                // normalize existing sets
                const existingIds = (window.KLIEN_EXISTING_IDS || []).map(s => String(s).toUpperCase());
                const existingEmails = (window.KLIEN_EXISTING_EMAILS || []).map(s => String(s).toLowerCase());
                const existingNames = (window.KLIEN_EXISTING_NAMES || []).map(s => String(s).toLowerCase());
                const existingPhones = (window.KLIEN_EXISTING_PHONES || []).map(s => String(s)); // sudah digit di DB

                function validate(el) {
                    const name = el.name;
                    const raw = String(el.value || '');
                    const v = raw.trim();
                    let msg = '';

                    // ensure feedback container
                    if (!el.parentElement.querySelector('.invalid-feedback')) {
                        const fb = document.createElement('div');
                        fb.className = 'invalid-feedback';
                        el.parentElement.appendChild(fb);
                    }

                    // required
                    if (!msg && el.hasAttribute('required') && !v) msg = 'Field ini wajib diisi.';

                    // maxlen generic
                    if (!msg && el.dataset.maxlen) {
                        const mx = parseInt(el.dataset.maxlen, 10);
                        if (v.length > mx) msg = `Maksimal ${mx} karakter.`;
                    }

                    // khusus ID (prefix + digit-only suffix + unik)
                    if (!msg && name === 'id_klien') {
                        const up = v.toUpperCase();
                        if (!up.startsWith('KL')) msg = 'ID harus diawali "KL".';
                        const suffix = up.slice(2);
                        if (!msg && /[^0-9]/.test(suffix)) msg = 'Setelah "KL" hanya boleh angka.';
                        if (!msg && existingIds.includes(up)) msg = 'ID klien sudah digunakan.';
                    }

                    // nama (maks 40 + unik case-insensitive)
                    if (!msg && name === 'nama_klien') {
                        if (v.length > 40) msg = 'Nama maksimal 40 karakter.';
                        else if (existingNames.includes(v.toLowerCase())) msg = 'Nama klien sudah digunakan.';
                    }

                    // telepon (digit-only, max 13, unik)
                    if (!msg && name === 'no_telepon_klien') {
                        if (!/^\d+$/.test(v)) msg = 'Nomor telepon hanya boleh angka.';
                        else if (v.length > 13) msg = 'Nomor telepon maksimal 13 digit.';
                        else if (existingPhones.includes(v)) msg = 'Nomor telepon sudah digunakan.';
                    }

                    // email (harus ada @, unik case-insensitive)
                    if (!msg && name === 'email_klien') {
                        if (!v.includes('@')) msg = 'Email harus mengandung "@".';
                        else if (existingEmails.includes(v.toLowerCase())) msg = 'Email sudah digunakan.';
                    }

                    if (msg) setInvalid(el, msg);
                    else setValid(el);
                }

                // pasang listener
                form.querySelectorAll('input, textarea, select').forEach(el => {
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

                // submit guard
                form.addEventListener('submit', (e) => {
                    form.querySelectorAll('input, textarea, select').forEach(validate);
                    if (form.querySelector('.is-invalid')) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                });

                // reset
                document.querySelector('[data-reset-form]')?.addEventListener('click', () => {
                    form.reset();
                    const idEl = form.querySelector('input[name="id_klien"][data-prefix]');
                    if (idEl) idEl.value = (idEl.dataset.prefix || 'KL').toUpperCase();
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                });
            })();
        </script>

    </div>
</div>