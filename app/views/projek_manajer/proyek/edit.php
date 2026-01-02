<?php
// app/views/projek_manajer/proyek/edit.php

$__updErr = $_SESSION['form_errors']['proyek_update'] ?? [];
$__updOld = $_SESSION['form_old']['proyek_update'] ?? [];
unset($_SESSION['form_errors']['proyek_update'], $_SESSION['form_old']['proyek_update']);

$raw = function (string $k, string $def = '') use ($__updOld, $proyek) {
    return (string)($__updOld[$k] ?? ($proyek[$k] ?? $def));
};
$esc = fn($v) => htmlspecialchars((string)$v);
?>
<div class="page-content-wrapper">
    <div class="page-content">

        <?php include __DIR__ . '/../../../partials/alert.php'; ?>

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item">
                        <a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= $BASE_URL ?>index.php?r=proyek">Manajemen Proyek</a>
                    </li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Proyek: <?= $esc($proyek['nama_proyek'] ?? '') ?></h5>
                <hr />

                <form class="row g-3 live-validate" novalidate
                    method="POST" action="<?= $BASE_URL ?>index.php?r=proyek/update"
                    enctype="multipart/form-data">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="id_proyek" value="<?= $esc($proyek['id_proyek'] ?? '') ?>">

                    <div class="col-md-6">
                        <label class="form-label">ID Proyek</label>
                        <input type="text" class="form-control" value="<?= $esc($proyek['id_proyek'] ?? '') ?>" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Quotation</label>
                        <input type="text" class="form-control" value="<?= $esc($proyek['quotation'] ?? '') ?>" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nama Proyek</label>
                        <input type="text" name="nama_proyek" required data-maxlen="45" data-unique="nama"
                            data-current-name="<?= $esc($CURRENT_NAME ?? '') ?>"
                            class="form-control <?= isset($__updErr['nama_proyek']) ? 'is-invalid' : '' ?>"
                            value="<?= $esc($raw('nama_proyek')) ?>">
                        <div class="invalid-feedback"><?= $__updErr['nama_proyek'] ?? 'Wajib, unik, maksimal 45 karakter.' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Klien</label>
                        <select name="klien_id_klien" required
                            class="form-select <?= isset($__updErr['klien_id_klien']) ? 'is-invalid' : '' ?>">
                            <option value="">-- pilih klien --</option>
                            <?php foreach ($klienList as $k): ?>
                                <option value="<?= $esc($k['id_klien']) ?>" <?= ($raw('klien_id_klien') == $k['id_klien']) ? 'selected' : '' ?>>
                                    <?= $esc($k['nama_klien']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $__updErr['klien_id_klien'] ?? 'Klien wajib dipilih.' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">PIC Site (Mandor)</label>
                        <select name="karyawan_id_pic_site" required
                            class="form-select <?= isset($__updErr['karyawan_id_pic_site']) ? 'is-invalid' : '' ?>">
                            <option value="">-- pilih mandor --</option>
                            <?php foreach ($mandorList as $m): ?>
                                <option value="<?= $esc($m['id_karyawan']) ?>" <?= ($raw('karyawan_id_pic_site') == $m['id_karyawan']) ? 'selected' : '' ?>>
                                    <?= $esc($m['nama_karyawan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $__updErr['karyawan_id_pic_site'] ?? 'PIC Site wajib dipilih.' ?></div>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-info py-2 mb-0">
                            <small><b>PIC Sales tidak diedit.</b> PIC Sales tetap = pembuat proyek, dan hanya dia yang bisa input pembayaran.</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Total Biaya Proyek</label>
                        <input type="text" name="total_biaya_proyek" required data-currency="rupiah"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="Rp 0"
                            class="form-control <?= isset($__updErr['total_biaya_proyek']) ? 'is-invalid' : '' ?>"
                            value="<?= $esc($raw('total_biaya_proyek')) ?>">
                        <div class="invalid-feedback"><?= $__updErr['total_biaya_proyek'] ?? 'Wajib & harus angka.' ?></div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" required
                            class="form-control <?= isset($__updErr['deskripsi']) ? 'is-invalid' : '' ?>"><?= $esc($raw('deskripsi')) ?></textarea>
                        <div class="invalid-feedback"><?= $__updErr['deskripsi'] ?? 'Wajib diisi.' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Alamat Proyek</label>
                        <input type="text" name="alamat" required
                            class="form-control <?= isset($__updErr['alamat']) ? 'is-invalid' : '' ?>"
                            value="<?= $esc($raw('alamat')) ?>">
                        <div class="invalid-feedback"><?= $__updErr['alamat'] ?? 'Wajib diisi.' ?></div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" required
                            class="form-control <?= isset($__updErr['tanggal_mulai']) ? 'is-invalid' : '' ?>"
                            value="<?= $esc($raw('tanggal_mulai')) ?>">
                        <div class="invalid-feedback"><?= $__updErr['tanggal_mulai'] ?? 'Tanggal wajib & valid.' ?></div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" required
                            class="form-control <?= isset($__updErr['tanggal_selesai']) ? 'is-invalid' : '' ?>"
                            value="<?= $esc($raw('tanggal_selesai')) ?>">
                        <div class="invalid-feedback"><?= $__updErr['tanggal_selesai'] ?? 'Tanggal wajib & valid.' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Gambar Kerja (opsional: ganti)</label>
                        <input type="file" name="gambar_kerja" accept=".jpg,.jpeg,.png,.heic"
                            class="form-control <?= isset($__updErr['gambar_kerja']) ? 'is-invalid' : '' ?>">
                        <div class="invalid-feedback"><?= $__updErr['gambar_kerja'] ?? 'Tipe file harus JPG/JPEG/PNG/HEIC.' ?></div>

                        <?php if (!empty($proyek['gambar_kerja'])): ?>
                            <?php
                            $rel = ltrim((string)$proyek['gambar_kerja'], '/');
                            $rel = preg_replace('#^public/#', '', $rel);
                            $abs = __DIR__ . '/../../../' . $rel;
                            $exists = is_file($abs);
                            ?>
                            <div class="form-text">
                                Saat ini:
                                <?php if ($exists): ?>
                                    <a href="<?= $BASE_URL . $esc($rel) ?>" target="_blank" rel="noopener">Lihat file</a>
                                <?php else: ?>
                                    <span class="text-danger">File <code><?= $esc(basename($rel)) ?></code> tidak ditemukan. Upload ulang bila perlu.</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- PM TIDAK BOLEH UBAH STATUS MANUAL -->
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input type="text" class="form-control" value="<?= $esc($raw('status')) ?>" disabled>
                        <div class="form-text">Status akan berubah otomatis saat progres/tahapan mulai berjalan.</div>
                    </div>

                    <div class="col-12">
                        <a href="<?= $BASE_URL ?>index.php?r=proyek" class="btn btn-light px-4">Kembali</a>
                        <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            (() => {
                window.PROJ_EXISTING_NAMES = <?= $EXISTING_NAMES_JSON ?? '[]' ?>;
                window.PROJ_CURRENT_NAME = <?= json_encode($CURRENT_NAME ?? '', JSON_UNESCAPED_UNICODE) ?>;

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

                        if (!msg && el.dataset.unique === 'nama' && val) {
                            const list = (window.PROJ_EXISTING_NAMES || []).map(s => String(s).toLowerCase());
                            const cur = (el.dataset.currentName || window.PROJ_CURRENT_NAME || '').toLowerCase();
                            if (val.toLowerCase() !== cur && list.includes(val.toLowerCase())) msg = 'Nama proyek sudah digunakan.';
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