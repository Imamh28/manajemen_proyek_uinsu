<?php
// Variabel: $tahapan, $BASE_URL, $EXISTING_IDS_JSON, $EXISTING_NAMES_JSON
$__storeErrors = $_SESSION['form_errors']['tahapan_store'] ?? [];
$__storeOld    = $_SESSION['form_old']['tahapan_store']    ?? [];
unset($_SESSION['form_errors']['tahapan_store'], $_SESSION['form_old']['tahapan_store']);
?>
<div class="page-content-wrapper">
    <div class="page-content">

        <?php include __DIR__ . '/../../../partials/alert.php'; ?>

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Data Master</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item">
                        <a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Daftar Tahapan</li>
                </ol>
            </div>
        </div>

        <!-- FORM TAMBAH -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Tambah Tahapan</h5>
                <hr />
                <form class="row g-3 live-validate" novalidate method="POST" action="<?= $BASE_URL ?>index.php?r=tahapan/store">
                    <?= csrf_input(); ?>

                    <div class="col-md-6">
                        <label class="form-label">ID Tahapan</label>
                        <input type="text" name="id_tahapan" required data-unique="id" data-prefix="TH"
                            class="form-control <?= isset($__storeErrors['id_tahapan']) ? 'is-invalid' : '' ?>"
                            placeholder="TH001"
                            value="<?= htmlspecialchars($__storeOld['id_tahapan'] ?? 'TH') ?>">
                        <div class="invalid-feedback">
                            <?= $__storeErrors['id_tahapan'] ?? 'ID wajib diawali "TH", hanya angka setelahnya, dan unik.' ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nama Tahapan</label>
                        <input type="text" name="nama_tahapan" required data-maxlen="45" data-unique="nama"
                            class="form-control <?= isset($__storeErrors['nama_tahapan']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($__storeOld['nama_tahapan'] ?? '') ?>">
                        <div class="invalid-feedback">
                            <?= $__storeErrors['nama_tahapan'] ?? 'Nama wajib, maksimal 45 karakter, dan unik.' ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary">Tambah Tahapan</button>
                        <button type="button" class="btn btn-outline-secondary ms-2" data-reset-form>Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- LIST -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0">Daftar Tahapan</h5>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <form action="<?= $BASE_URL ?>index.php" method="GET" class="d-flex">
                            <input type="hidden" name="r" value="tahapan">
                            <input type="text" class="form-control" name="search" placeholder="Cari..."
                                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width:250px">
                            <button class="btn btn-primary ms-2">Cari</button>
                        </form>
                        <a class="btn btn-outline-primary"
                            href="<?= $BASE_URL ?>index.php?r=tahapan/export&search=<?= urlencode($_GET['search'] ?? '') ?>">
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
                                <th>Nama Tahapan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tahapan)): foreach ($tahapan as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['id_tahapan']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_tahapan']) ?></td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <a class="ms-3" href="<?= $BASE_URL ?>index.php?r=tahapan/edit&id=<?= urlencode($row['id_tahapan']) ?>">
                                                    <ion-icon name="create-outline"></ion-icon>
                                                </a>
                                                <a class="ms-3" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                    data-id="<?= htmlspecialchars($row['id_tahapan']) ?>">
                                                    <ion-icon name="trash-outline"></ion-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">Belum ada data.</td>
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
                <form class="modal-content" method="POST" action="<?= $BASE_URL ?>index.php?r=tahapan/delete">
                    <?= csrf_input(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="hapus_id" id="hapus_id">
                        Hapus tahapan ini?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // modal hapus
            const modalEl = document.getElementById('modalHapus');
            if (modalEl) modalEl.addEventListener('show.bs.modal', e => {
                const id = e.relatedTarget?.getAttribute('data-id') || '';
                document.getElementById('hapus_id').value = id;
            });

            // data unik untuk validasi live
            window.EXISTING_IDS = <?= $EXISTING_IDS_JSON   ?? '[]' ?>;
            window.EXISTING_NAMES = <?= $EXISTING_NAMES_JSON ?? '[]' ?>;

            (function() {
                const debounce = (fn, wait = 250) => {
                    let t;
                    return (...a) => {
                        clearTimeout(t);
                        t = setTimeout(() => fn(...a), wait);
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

                /* ====== Kunci prefix "TH" + hanya angka sesudahnya ====== */
                const idInput = form.querySelector('input[name="id_tahapan"][data-prefix]');
                if (idInput) {
                    const PFX = (idInput.dataset.prefix || 'TH').toUpperCase();
                    const NAV = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'];

                    if (!idInput.value || !idInput.value.toUpperCase().startsWith(PFX)) idInput.value = PFX;

                    idInput.addEventListener('keydown', (ev) => {
                        const start = idInput.selectionStart ?? 0;
                        const end = idInput.selectionEnd ?? start;

                        if (NAV.includes(ev.key) || ev.ctrlKey || ev.metaKey || ev.altKey) return;

                        // lindungi area prefix
                        if (start < PFX.length) {
                            if (ev.key === 'Backspace' || ev.key === 'Delete') {
                                ev.preventDefault();
                                return;
                            }
                            if (ev.key.length === 1) idInput.setSelectionRange(PFX.length, PFX.length);
                        }

                        // hanya digit yang boleh diketik
                        if (ev.key.length === 1 && !/^\d$/.test(ev.key)) ev.preventDefault();
                    });

                    idInput.addEventListener('input', () => {
                        let v = (idInput.value || '').toUpperCase();
                        if (!v.startsWith(PFX)) v = PFX + v.replace(/^TH/i, '');
                        const digits = v.slice(PFX.length).replace(/\D+/g, '');
                        idInput.value = PFX + digits;
                        validateField(idInput);
                    });

                    idInput.addEventListener('paste', (ev) => {
                        ev.preventDefault();
                        const t = (ev.clipboardData || window.clipboardData).getData('text') || '';
                        const digits = t.replace(/^TH/i, '').replace(/\D+/g, '');

                        const start = idInput.selectionStart ?? idInput.value.length;
                        const end = idInput.selectionEnd ?? start;

                        const suffixNow = idInput.value.toUpperCase().slice(PFX.length);
                        const before = suffixNow.slice(0, Math.max(0, start - PFX.length));
                        const after = suffixNow.slice(Math.max(0, end - PFX.length));

                        const merged = (before + digits + after).replace(/\D+/g, '');
                        idInput.value = PFX + merged;

                        const caret = PFX.length + before.length + digits.length;
                        idInput.setSelectionRange(caret, caret);
                        validateField(idInput);
                    });
                }
                /* ======================================================== */

                function validateField(el) {
                    const val = (el.value || '').trim();
                    let msg = '';

                    if (el.hasAttribute('required') && !val) msg = 'Field ini wajib diisi.';

                    if (!msg && el.dataset.maxlen) {
                        const mx = parseInt(el.dataset.maxlen, 10);
                        if (val.length > mx) msg = `Maksimal ${mx} karakter.`;
                    }

                    // ID: 'TH' + hanya angka + unik
                    if (!msg && el.name === 'id_tahapan') {
                        const PFX = (el.dataset.prefix || 'TH').toUpperCase();
                        const up = val.toUpperCase();
                        if (!up.startsWith(PFX)) msg = `ID harus diawali "${PFX}".`;
                        const suffix = up.slice(PFX.length);
                        if (!msg && /[^0-9]/.test(suffix)) msg = 'Setelah "TH" hanya boleh angka.';
                        if (!msg) {
                            const list = (window.EXISTING_IDS || []).map(String);
                            if (list.includes(up)) msg = 'ID sudah digunakan.';
                        }
                    }

                    // Nama: unik (list sudah lowercase)
                    if (!msg && el.dataset.unique === 'nama') {
                        const list = (window.EXISTING_NAMES || []);
                        if (list.includes(val.toLowerCase())) msg = 'Nama tahapan sudah digunakan.';
                    }

                    if (msg) setInvalid(el, msg);
                    else setValid(el);
                }

                // pasang listener
                form.querySelectorAll('input,select,textarea').forEach(el => {
                    if (!el.parentElement.querySelector('.invalid-feedback')) {
                        const fb = document.createElement('div');
                        fb.className = 'invalid-feedback';
                        el.parentElement.appendChild(fb);
                    }
                    const run = debounce(() => validateField(el), 200);
                    el.addEventListener('input', run);
                    el.addEventListener('blur', () => validateField(el));
                    el.addEventListener('change', () => validateField(el));
                });

                // submit guard + spinner
                form.addEventListener('submit', (e) => {
                    form.querySelectorAll('input,select,textarea').forEach(el => validateField(el));
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
                    const idEl = form.querySelector('input[name="id_tahapan"][data-prefix]');
                    if (idEl) idEl.value = (idEl.dataset.prefix || 'TH').toUpperCase();
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                });
            })();
        </script>

    </div>
</div>