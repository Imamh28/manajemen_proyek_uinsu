<?php
// app/views/projek_manajer/proyek/main.php

$__storeErr = $_SESSION['form_errors']['proyek_store'] ?? [];
$__storeOld = $_SESSION['form_old']['proyek_store'] ?? [];
unset($_SESSION['form_errors']['proyek_store'], $_SESSION['form_old']['proyek_store']);
?>
<div class="page-content-wrapper">
    <div class="page-content">

        <?php include __DIR__ . '/../../../partials/alert.php'; ?>

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 align-items-center">
                        <li class="breadcrumb-item">
                            <a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Manajemen Proyek</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- FORM CREATE: Project Manager -->
        <form class="row g-3 live-validate" novalidate
            action="<?= $BASE_URL ?>index.php?r=proyek/store"
            method="POST" enctype="multipart/form-data">
            <?= csrf_input(); ?>

            <div class="col-md-6">
                <label class="form-label">ID Proyek</label>
                <input type="text"
                    name="id_proyek"
                    required
                    data-unique="id"
                    data-prefix="PRJ"
                    class="form-control <?= isset($__storeErr['id_proyek']) ? 'is-invalid' : '' ?>"
                    placeholder="PRJ001"
                    value="<?= htmlspecialchars($__storeOld['id_proyek'] ?? 'PRJ') ?>">
                <div class="invalid-feedback">
                    <?= $__storeErr['id_proyek'] ?? 'Wajib diawali "PRJ" & unik.' ?>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nama Proyek</label>
                <input type="text"
                    name="nama_proyek"
                    required
                    data-maxlen="45"
                    data-unique="nama"
                    class="form-control <?= isset($__storeErr['nama_proyek']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['nama_proyek'] ?? '') ?>">
                <div class="invalid-feedback">
                    <?= $__storeErr['nama_proyek'] ?? 'Wajib, unik, maksimal 45 karakter.' ?>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Quotation</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($NEXT_QUOTATION ?? '') ?>" disabled>
                <div class="form-text">Dibuat otomatis saat disimpan.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Status</label>
                <input type="text" class="form-control" value="Menunggu" disabled>
                <div class="form-text">Status awal dipaksa "Menunggu".</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Klien</label>
                <select name="klien_id_klien" required
                    class="form-select <?= isset($__storeErr['klien_id_klien']) ? 'is-invalid' : '' ?>">
                    <option value="">-- pilih klien --</option>
                    <?php foreach ($klienList as $k): ?>
                        <option value="<?= htmlspecialchars($k['id_klien']) ?>"
                            <?= (($__storeOld['klien_id_klien'] ?? '') == $k['id_klien']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_klien']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= $__storeErr['klien_id_klien'] ?? 'Klien wajib dipilih.' ?></div>
            </div>

            <!-- PIC SITE: MANDOR SAJA + unik -->
            <div class="col-md-6">
                <label class="form-label">PIC Site (Mandor)</label>
                <select name="karyawan_id_pic_site" required
                    class="form-select <?= isset($__storeErr['karyawan_id_pic_site']) ? 'is-invalid' : '' ?>">
                    <option value="">-- pilih mandor --</option>
                    <?php foreach ($mandorList as $m): ?>
                        <option value="<?= htmlspecialchars($m['id_karyawan']) ?>"
                            <?= (($__storeOld['karyawan_id_pic_site'] ?? '') == $m['id_karyawan']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['nama_karyawan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    <?= $__storeErr['karyawan_id_pic_site'] ?? 'PIC Site wajib dipilih (mandor yang belum terikat proyek aktif).' ?>
                </div>
            </div>

            <div class="col-12">
                <div class="alert alert-info py-2 mb-0">
                    <small>
                        <b>PIC Sales tidak diinput.</b> Sistem otomatis menyimpan PIC Sales = user yang membuat proyek.
                        Pembayaran hanya bisa diinput oleh PIC Sales tersebut.
                    </small>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Total Biaya Proyek</label>
                <input type="text"
                    name="total_biaya_proyek"
                    required
                    data-currency="rupiah"
                    inputmode="numeric"
                    autocomplete="off"
                    placeholder="Rp 0"
                    class="form-control <?= isset($__storeErr['total_biaya_proyek']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['total_biaya_proyek'] ?? '') ?>">
                <div class="invalid-feedback">
                    <?= $__storeErr['total_biaya_proyek'] ?? 'Wajib & harus angka.' ?>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" rows="3" required
                    class="form-control <?= isset($__storeErr['deskripsi']) ? 'is-invalid' : '' ?>"><?= htmlspecialchars($__storeOld['deskripsi'] ?? '') ?></textarea>
                <div class="invalid-feedback"><?= $__storeErr['deskripsi'] ?? 'Wajib diisi.' ?></div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Alamat Proyek</label>
                <input type="text"
                    name="alamat"
                    required
                    class="form-control <?= isset($__storeErr['alamat']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['alamat'] ?? '') ?>">
                <div class="invalid-feedback"><?= $__storeErr['alamat'] ?? 'Wajib diisi.' ?></div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date"
                    name="tanggal_mulai"
                    required
                    class="form-control <?= isset($__storeErr['tanggal_mulai']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['tanggal_mulai'] ?? '') ?>">
                <div class="invalid-feedback"><?= $__storeErr['tanggal_mulai'] ?? 'Tanggal wajib & valid.' ?></div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date"
                    name="tanggal_selesai"
                    required
                    class="form-control <?= isset($__storeErr['tanggal_selesai']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['tanggal_selesai'] ?? '') ?>">
                <div class="invalid-feedback"><?= $__storeErr['tanggal_selesai'] ?? 'Tanggal wajib & valid.' ?></div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Gambar Kerja</label>
                <input type="file"
                    name="gambar_kerja"
                    accept=".jpg,.jpeg,.png,.heic"
                    class="form-control <?= isset($__storeErr['gambar_kerja']) ? 'is-invalid' : '' ?>">
                <div class="invalid-feedback"><?= $__storeErr['gambar_kerja'] ?? 'Tipe file harus JPG/JPEG/PNG/HEIC.' ?></div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4">Simpan</button>
            </div>
        </form>

        <hr>

        <!-- LIST -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Proyek</h5>
                    <a class="btn btn-outline-secondary" href="<?= $BASE_URL ?>index.php?r=proyek/export">Export CSV</a>
                </div>
                <hr>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nama Proyek</th>
                                <th>Klien</th>
                                <th>Total Biaya</th>
                                <th>Status</th>
                                <th>PIC Sales</th>
                                <th>PIC Site</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($proyek)): foreach ($proyek as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['id_proyek']) ?></td>
                                        <td><?= htmlspecialchars($p['nama_proyek']) ?></td>
                                        <td><?= htmlspecialchars($p['nama_klien'] ?? '-') ?></td>
                                        <td>Rp <?= number_format((float)($p['total_biaya_proyek'] ?? 0), 0, ',', '.') ?></td>
                                        <td>
                                            <?php
                                            $st = $p['status'] ?? '';
                                            $badge = ($st === 'Selesai') ? 'success'
                                                : (($st === 'Berjalan') ? 'warning'
                                                    : (($st === 'Dibatalkan') ? 'danger' : 'info'));
                                            ?>
                                            <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($st) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($p['nama_sales'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($p['nama_site'] ?? '-') ?></td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <a class="ms-3" href="<?= $BASE_URL ?>index.php?r=proyek/edit&id=<?= urlencode($p['id_proyek']) ?>">
                                                    <ion-icon name="create-outline"></ion-icon>
                                                </a>
                                                <!-- PM: tidak ada tombol hapus -->
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada data.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
            (() => {
                // live validate
                window.PROJ_EXISTING_IDS = <?= $EXISTING_IDS_JSON ?? '[]' ?>;
                window.PROJ_EXISTING_NAMES = <?= $EXISTING_NAMES_JSON ?? '[]' ?>;

                const digitsOnly = (v) => (String(v || '').match(/\d+/g) || []).join('');

                const formatIdNumber = (digits) => {
                    if (!digits) return '';
                    try {
                        return new Intl.NumberFormat('id-ID').format(BigInt(digits));
                    } catch (e) {
                        const n = parseInt(digits, 10) || 0;
                        return new Intl.NumberFormat('id-ID').format(n);
                    }
                };

                const setCaretByDigitIndex = (el, digitIndex) => {
                    const v = el.value || '';
                    if (digitIndex <= 0) {
                        const p = v.startsWith('Rp ') ? 3 : 0;
                        el.setSelectionRange(p, p);
                        return;
                    }
                    let seen = 0;
                    for (let i = 0; i < v.length; i++) {
                        if (/\d/.test(v[i])) {
                            seen++;
                            if (seen === digitIndex) {
                                el.setSelectionRange(i + 1, i + 1);
                                return;
                            }
                        }
                    }
                    el.setSelectionRange(v.length, v.length);
                };

                const attachRupiahFormatter = (form) => {
                    const rupiahInputs = form.querySelectorAll('input[data-currency="rupiah"]');

                    rupiahInputs.forEach((el) => {
                        const initDigits = digitsOnly(el.value);
                        el.value = initDigits ? ('Rp ' + formatIdNumber(initDigits)) : '';

                        const formatNow = () => {
                            if (el.__fmtLock) return;
                            el.__fmtLock = true;

                            const raw = el.value || '';
                            const caret = (typeof el.selectionStart === 'number') ? el.selectionStart : raw.length;
                            const digitsBefore = (raw.slice(0, caret).match(/\d/g) || []).length;

                            const digits = digitsOnly(raw);
                            if (!digits) {
                                el.value = '';
                                el.__fmtLock = false;
                                return;
                            }

                            el.value = 'Rp ' + formatIdNumber(digits);

                            try {
                                setCaretByDigitIndex(el, digitsBefore);
                            } catch (e) {}
                            el.__fmtLock = false;
                        };

                        el.addEventListener('input', formatNow);
                        el.addEventListener('blur', () => {
                            if (!digitsOnly(el.value)) el.value = '';
                        });
                    });

                    form.addEventListener('submit', () => {
                        rupiahInputs.forEach(el => {
                            el.value = digitsOnly(el.value);
                        });
                    });
                };

                document.querySelectorAll('.live-validate').forEach(form => {
                    const inputs = form.querySelectorAll('input,select,textarea');
                    const getDigits = digitsOnly;

                    attachRupiahFormatter(form);

                    function setInvalid(el, msg) {
                        el.classList.add('is-invalid');
                        const fb = el.parentElement.querySelector('.invalid-feedback');
                        if (fb && msg) fb.textContent = msg;
                    }

                    function setValid(el) {
                        el.classList.remove('is-invalid');
                    }

                    function validate(el) {
                        if (!(el instanceof HTMLElement)) return;
                        const val = (el.value || '').trim();
                        let msg = '';

                        if (!msg && el.hasAttribute('required') && !val) msg = 'Wajib diisi.';

                        if (!msg && el.dataset.maxlen) {
                            const mx = parseInt(el.dataset.maxlen, 10);
                            if (val.length > mx) msg = `Maksimal ${mx} karakter.`;
                        }

                        if (!msg && el.dataset.unique === 'id' && val) {
                            if (!val.startsWith('PRJ')) msg = 'ID harus diawali "PRJ".';
                            if ((window.PROJ_EXISTING_IDS || []).map(String).includes(String(val))) msg = 'ID proyek sudah digunakan.';
                        }

                        if (!msg && el.dataset.unique === 'nama' && val) {
                            const list = (window.PROJ_EXISTING_NAMES || []).map(s => String(s).toLowerCase());
                            if (list.includes(val.toLowerCase())) msg = 'Nama proyek sudah digunakan.';
                        }

                        if (!msg && el.dataset.currency === 'rupiah' && val) {
                            if (!getDigits(val)) msg = 'Total biaya harus angka.';
                        }

                        if (!msg && el.type === 'file' && el.files && el.files.length) {
                            const ok = Array.from(el.files).every(f => {
                                const ext = (f.name.split('.').pop() || '').toLowerCase();
                                return ['jpg', 'jpeg', 'png', 'heic'].includes(ext);
                            });
                            if (!ok) msg = 'Tipe file harus JPG/JPEG/PNG/HEIC.';
                        }

                        if (msg) setInvalid(el, msg);
                        else setValid(el);
                    }

                    inputs.forEach(el => {
                        el.addEventListener('input', () => validate(el));
                        el.addEventListener('change', () => validate(el));
                    });
                });
            })();
        </script>

    </div>
</div>