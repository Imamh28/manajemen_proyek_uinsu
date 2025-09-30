<?php
// Variabel dari controller: $roles, $karyawan,
// $EXISTING_IDS_JSON, $EXISTING_EMAILS_JSON, $EXISTING_PHONES_JSON, $BASE_URL
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
                        <li class="breadcrumb-item active" aria-current="page">Data Karyawan</li>
                    </ol>
                </nav>
            </div>
        </div>

        <?php
        $__storeErrors = $_SESSION['form_errors']['karyawan_store'] ?? [];
        $__storeOld    = $_SESSION['form_old']['karyawan_store']    ?? [];
        unset($_SESSION['form_errors']['karyawan_store'], $_SESSION['form_old']['karyawan_store']);
        ?>

        <!-- FORM TAMBAH -->
        <form class="row g-3 live-validate" novalidate action="<?= $BASE_URL ?>index.php?r=karyawan/store" method="POST">
            <?= csrf_input(); ?>

            <div class="col-md-6">
                <label class="form-label">ID Karyawan</label>
                <input type="text" name="id_karyawan" required data-unique="id" data-prefix="KR"
                    placeholder="KR001"
                    class="form-control <?= isset($__storeErrors['id_karyawan']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['id_karyawan'] ?? 'KR') ?>">
                <div class="invalid-feedback">
                    <?= $__storeErrors['id_karyawan'] ?? 'ID karyawan wajib diawali "KR" & unik.' ?>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nama Karyawan</label>
                <input type="text" name="nama_karyawan" required data-maxlen="20"
                    class="form-control <?= isset($__storeErrors['nama_karyawan']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['nama_karyawan'] ?? '') ?>">
                <div class="invalid-feedback">
                    <?= $__storeErrors['nama_karyawan'] ?? 'Nama maksimal 20 karakter.' ?>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nomor Telepon</label>
                <input type="tel" name="no_telepon_karyawan" required data-unique="phone" inputmode="numeric" pattern="\d*"
                    data-digits="true" data-maxlen="13"
                    class="form-control <?= isset($__storeErrors['no_telepon_karyawan']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['no_telepon_karyawan'] ?? '') ?>">
                <div class="invalid-feedback">
                    <?= $__storeErrors['no_telepon_karyawan'] ?? 'Nomor telepon wajib, hanya angka, maks. 13 digit, dan harus unik.' ?>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" required data-unique="email"
                    class="form-control <?= isset($__storeErrors['email']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['email'] ?? '') ?>">
                <div class="invalid-feedback">
                    <?= $__storeErrors['email'] ?? 'Email wajib/valid dan unik.' ?>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" name="password" required data-minlen="8"
                    class="form-control <?= isset($__storeErrors['password']) ? 'is-invalid' : '' ?>">
                <div class="invalid-feedback">
                    <?= $__storeErrors['password'] ?? 'Password minimal 8 karakter.' ?>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select name="role_id_role" required
                    class="form-select <?= isset($__storeErrors['role_id_role']) ? 'is-invalid' : '' ?>">
                    <option value="" disabled <?= empty($__storeOld['role_id_role']) ? 'selected' : '' ?>>Pilih Role...</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= htmlspecialchars($r['id_role']) ?>"
                            <?= (($__storeOld['role_id_role'] ?? '') == $r['id_role']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['nama_role']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    <?= $__storeErrors['role_id_role'] ?? 'Silakan pilih role.' ?>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4">Tambah Karyawan</button>
                <button type="button" class="btn btn-outline-secondary ms-2" data-reset-form>Reset</button>
            </div>
        </form>

        <!-- LIST -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0">Daftar Karyawan</h5>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <form action="<?= $BASE_URL ?>index.php" method="GET" class="d-flex">
                            <input type="hidden" name="r" value="karyawan">
                            <input type="text" class="form-control" name="search" placeholder="Cari karyawan..."
                                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width:250px">
                            <button type="submit" class="btn btn-primary ms-2">Cari</button>
                        </form>
                        <a class="btn btn-outline-primary"
                            href="<?= $BASE_URL ?>index.php?r=karyawan/export&search=<?= urlencode($_GET['search'] ?? '') ?>">
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
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($karyawan)): foreach ($karyawan as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['id_karyawan']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_karyawan']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon_karyawan']) ?></td>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($row['nama_role']) ?></span></td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <a class="ms-3" href="<?= $BASE_URL ?>index.php?r=karyawan/edit&id=<?= urlencode($row['id_karyawan']) ?>">
                                                    <ion-icon name="create-outline"></ion-icon>
                                                </a>
                                                <a class="ms-3" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                    data-id="<?= htmlspecialchars($row['id_karyawan']) ?>">
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
                <form class="modal-content" method="POST" action="<?= $BASE_URL ?>index.php?r=karyawan/delete">
                    <?= csrf_input(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="hapus_id" id="hapus_id">Apakah Anda yakin ingin menghapus karyawan ini?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            const modalEl = document.getElementById('modalHapus');
            if (modalEl) {
                modalEl.addEventListener('show.bs.modal', ev => {
                    const id = ev.relatedTarget?.getAttribute('data-id') || '';
                    const input = document.getElementById('hapus_id');
                    if (input) input.value = id;
                });
            }

            window.EXISTING_IDS = <?= $EXISTING_IDS_JSON    ?? '[]' ?>;
            window.EXISTING_EMAILS = <?= $EXISTING_EMAILS_JSON ?? '[]' ?>;
            window.EXISTING_PHONES = <?= $EXISTING_PHONES_JSON ?? '[]' ?>;

            const debounce = (fn, wait = 300) => {
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
                    const idEl = form.querySelector('[name="id_karyawan"][data-prefix="KR"]');
                    if (idEl && !idEl.value) idEl.value = 'KR';
                });

                // Live validation + kunci KR
                const forms = document.querySelectorAll('form.live-validate');
                forms.forEach(form => {
                    form.querySelectorAll('input, select, textarea').forEach(el => {
                        if (!el.parentElement.querySelector('.invalid-feedback')) {
                            const fb = document.createElement('div');
                            fb.className = 'invalid-feedback';
                            el.parentElement.appendChild(fb);
                        }

                        if (el.name === 'id_karyawan') {
                            const PFX = (el.dataset.prefix || 'KR').toUpperCase();
                            const NAV = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'];
                            if (!el.value || !el.value.toUpperCase().startsWith(PFX)) el.value = PFX;

                            el.addEventListener('keydown', ev => {
                                const start = el.selectionStart ?? 0;
                                if (NAV.includes(ev.key) || ev.ctrlKey || ev.metaKey || ev.altKey) return;
                                if (start < PFX.length) {
                                    if (ev.key === 'Backspace' || ev.key === 'Delete') {
                                        ev.preventDefault();
                                        return;
                                    }
                                    if (ev.key.length === 1) el.setSelectionRange(PFX.length, PFX.length);
                                }
                                if (ev.key.length === 1 && !/^\d$/.test(ev.key)) ev.preventDefault();
                            });

                            el.addEventListener('input', () => {
                                let v = (el.value || '').toUpperCase();
                                if (!v.startsWith(PFX)) v = PFX + v.replace(/^KR/i, '');
                                const digits = v.slice(PFX.length).replace(/\D+/g, '');
                                el.value = PFX + digits;
                                validateField(el);
                            });

                            el.addEventListener('paste', ev => {
                                ev.preventDefault();
                                const t = (ev.clipboardData || window.clipboardData).getData('text') || '';
                                const digits = t.replace(/^KR/i, '').replace(/\D+/g, '');
                                const start = el.selectionStart ?? el.value.length;
                                const end = el.selectionEnd ?? start;
                                const suffix = el.value.toUpperCase().slice(PFX.length);
                                const before = suffix.slice(0, Math.max(0, start - PFX.length));
                                const after = suffix.slice(Math.max(0, end - PFX.length));
                                el.value = PFX + (before + digits + after).replace(/\D+/g, '');
                                const caret = PFX.length + before.length + digits.length;
                                el.setSelectionRange(caret, caret);
                                validateField(el);
                            });
                        }

                        const run = debounce(() => validateField(el), 300);
                        el.addEventListener('input', run);
                        el.addEventListener('blur', () => validateField(el));
                        el.addEventListener('change', () => validateField(el));
                    });
                });

                function validateField(el) {
                    const fb = el.parentElement.querySelector('.invalid-feedback');
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
                    if (!msg && el.type === 'email' && val && !val.includes('@')) msg = 'Email harus mengandung "@".';
                    if (!msg && el.dataset.digits !== undefined && val && !/^\d+$/.test(val)) msg = 'Nomor telepon hanya boleh angka.';
                    if (!msg && el.name === 'id_karyawan') {
                        const PFX = (el.dataset.prefix || 'KR').toUpperCase();
                        if (!val.startsWith(PFX)) msg = `ID harus diawali "${PFX}".`;
                    }
                    if (!msg && el.dataset.unique === 'id' && val) {
                        const list = (window.EXISTING_IDS || []).map(String);
                        if (list.includes(String(val))) msg = 'ID karyawan sudah digunakan.';
                    }
                    if (!msg && el.dataset.unique === 'email' && val) {
                        const list = (window.EXISTING_EMAILS || []).map(s => String(s).toLowerCase());
                        const current = (el.dataset.currentEmail || '').toLowerCase();
                        const v = val.toLowerCase();
                        if (v !== current && list.includes(v)) msg = 'Email sudah digunakan.';
                    }
                    if (!msg && el.dataset.unique === 'phone' && val) {
                        const norm = String(val).replace(/\D+/g, '');
                        const curr = String(el.dataset.currentPhone || '').replace(/\D+/g, '');
                        const exists = (window.EXISTING_PHONES || []).map(p => String(p).replace(/\D+/g, ''));
                        if (norm && norm !== curr && exists.includes(norm)) msg = 'Nomor telepon sudah digunakan.';
                    }

                    if (msg) {
                        el.classList.add('is-invalid');
                        if (fb) fb.textContent = msg;
                    } else {
                        el.classList.remove('is-invalid');
                    }
                }
            });
        </script>

    </div>
</div>