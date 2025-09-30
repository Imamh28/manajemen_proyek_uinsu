<?php
// Variables from controller:
// $proyek (rows), $klienList, $brandList, $karyawanList, $statusEnum, $BASE_URL,
// $EXISTING_IDS_JSON, $EXISTING_NAMES_JSON, (opsional) $NEXT_QUOTATION

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
                        <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Manajemen Proyek</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- FORM -->
        <form class="row g-3 live-validate" novalidate action="<?= $BASE_URL ?>index.php?r=proyek/store" method="POST" enctype="multipart/form-data">
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
                <input type="text" name="nama_proyek" required data-maxlen="45" data-unique="nama"
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
                <label class="form-label">Total Biaya Proyek</label>
                <input type="text"
                    name="total_biaya_proyek"
                    required
                    data-currency="rupiah"
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
                <input type="text" name="alamat" required
                    class="form-control <?= isset($__storeErr['alamat']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['alamat'] ?? '') ?>">
                <div class="invalid-feedback"><?= $__storeErr['alamat'] ?? 'Wajib diisi.' ?></div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" required
                    class="form-control <?= isset($__storeErr['tanggal_mulai']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['tanggal_mulai'] ?? '') ?>">
                <div class="invalid-feedback"><?= $__storeErr['tanggal_mulai'] ?? 'Tanggal wajib & valid.' ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" required
                    class="form-control <?= isset($__storeErr['tanggal_selesai']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['tanggal_selesai'] ?? '') ?>">
                <div class="invalid-feedback"><?= $__storeErr['tanggal_selesai'] ?? 'Tanggal wajib & valid.' ?></div>
            </div>

            <!-- Gambar Kerja -->
            <div class="col-md-6">
                <label class="form-label">Gambar Kerja</label>
                <input type="file" name="gambar_kerja" accept=".jpg,.jpeg,.png,.heic"
                    class="form-control <?= isset($__storeErr['gambar_kerja']) ? 'is-invalid' : '' ?>">
                <div class="invalid-feedback"><?= $__storeErr['gambar_kerja'] ?? 'Tipe file harus JPG/JPEG/PNG/HEIC.' ?></div>
                <div class="form-text">Opsional.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select <?= isset($__storeErr['status']) ? 'is-invalid' : '' ?>" required>
                    <option value="" disabled <?= empty($__storeOld['status']) ? 'selected' : '' ?>>Pilih status...</option>
                    <?php foreach ($statusEnum as $s): ?>
                        <option value="<?= $s ?>" <?= (($__storeOld['status'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    <?= $__storeErr['status'] ?? 'Status wajib dipilih.' ?>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Klien</label>
                <select name="klien_id_klien" required class="form-select <?= isset($__storeErr['klien_id_klien']) ? 'is-invalid' : '' ?>">
                    <option value="" disabled <?= empty($__storeOld['klien_id_klien']) ? 'selected' : '' ?>>Pilih Klien...</option>
                    <?php foreach ($klienList as $k): ?>
                        <option value="<?= $k['id_klien'] ?>" <?= (($__storeOld['klien_id_klien'] ?? '') == $k['id_klien']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_klien']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= $__storeErr['klien_id_klien'] ?? 'Klien wajib dipilih.' ?></div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Brand</label>
                <select name="brand_id_brand" required class="form-select <?= isset($__storeErr['brand_id_brand']) ? 'is-invalid' : '' ?>">
                    <option value="" disabled <?= empty($__storeOld['brand_id_brand']) ? 'selected' : '' ?>>Pilih Brand...</option>
                    <?php foreach ($brandList as $b): ?>
                        <option value="<?= $b['id_brand'] ?>" <?= (($__storeOld['brand_id_brand'] ?? '') == $b['id_brand']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['nama_brand']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= $__storeErr['brand_id_brand'] ?? 'Brand wajib dipilih.' ?></div>
            </div>

            <div class="col-md-6">
                <label class="form-label">PIC Sales</label>
                <select name="karyawan_id_pic_sales" required class="form-select <?= isset($__storeErr['karyawan_id_pic_sales']) ? 'is-invalid' : '' ?>">
                    <option value="" disabled <?= empty($__storeOld['karyawan_id_pic_sales']) ? 'selected' : '' ?>>Pilih PIC Sales...</option>
                    <?php foreach ($karyawanList as $k): ?>
                        <option value="<?= $k['id_karyawan'] ?>" <?= (($__storeOld['karyawan_id_pic_sales'] ?? '') == $k['id_karyawan']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_karyawan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= $__storeErr['karyawan_id_pic_sales'] ?? 'PIC Sales wajib.' ?></div>
            </div>

            <div class="col-md-6">
                <label class="form-label">PIC Site</label>
                <select name="karyawan_id_pic_site" required class="form-select <?= isset($__storeErr['karyawan_id_pic_site']) ? 'is-invalid' : '' ?>">
                    <option value="" disabled <?= empty($__storeOld['karyawan_id_pic_site']) ? 'selected' : '' ?>>Pilih PIC Site...</option>
                    <?php foreach ($karyawanList as $k): ?>
                        <option value="<?= $k['id_karyawan'] ?>" <?= (($__storeOld['karyawan_id_pic_site'] ?? '') == $k['id_karyawan']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_karyawan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= $__storeErr['karyawan_id_pic_site'] ?? 'PIC Site wajib.' ?></div>
            </div>

            <div class="col-12 mb-2">
                <button class="btn btn-primary px-4" type="submit">Tambah Proyek</button>
                <button type="button" class="btn btn-outline-secondary ms-2" data-reset-form>Reset</button>
            </div>
        </form>

        <!-- spasi antar bagian -->
        <div class="my-3"></div>

        <!-- LIST -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0">Daftar Proyek</h5>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <form action="<?= $BASE_URL ?>index.php" method="GET" class="d-flex">
                            <input type="hidden" name="r" value="proyek">
                            <input type="text" class="form-control" name="search" placeholder="Cari..."
                                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width:250px">
                            <button class="btn btn-primary ms-2">Cari</button>
                        </form>
                        <a class="btn btn-outline-primary"
                            href="<?= $BASE_URL ?>index.php?r=proyek/export&search=<?= urlencode($_GET['search'] ?? '') ?>">
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
                                <th>Nama Proyek</th>
                                <th>Klien</th>
                                <th>Brand</th>
                                <th>Total Biaya</th>
                                <th>Status</th>
                                <th>PIC Sales</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($proyek)): foreach ($proyek as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['id_proyek']) ?></td>
                                        <td><?= htmlspecialchars($p['nama_proyek']) ?></td>
                                        <td><?= htmlspecialchars($p['nama_klien']) ?></td>
                                        <td><?= htmlspecialchars($p['nama_brand']) ?></td>
                                        <td>Rp <?= number_format((float)$p['total_biaya_proyek'], 0, ',', '.') ?></td>
                                        <td><span class="badge bg-<?= $p['status'] === 'Selesai' ? 'success' : ($p['status'] === 'Berjalan' ? 'warning' : ($p['status'] === 'Dibatalkan' ? 'danger' : 'info')) ?>">
                                                <?= htmlspecialchars($p['status']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($p['nama_sales']) ?></td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <a class="ms-3" href="<?= $BASE_URL ?>index.php?r=proyek/edit&id=<?= urlencode($p['id_proyek']) ?>">
                                                    <ion-icon name="create-outline"></ion-icon>
                                                </a>
                                                <a class="ms-3" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                    data-id="<?= htmlspecialchars($p['id_proyek']) ?>">
                                                    <ion-icon name="trash-outline"></ion-icon>
                                                </a>
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

        <!-- Modal Hapus -->
        <div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="<?= $BASE_URL ?>index.php?r=proyek/delete">
                    <?= csrf_input(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="hapus_id" id="hapus_id">
                        Hapus proyek ini?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-danger" type="submit">Hapus</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            window.PROJ_EXISTING_IDS = <?= $EXISTING_IDS_JSON   ?? '[]' ?>;
            window.PROJ_EXISTING_NAMES = <?= $EXISTING_NAMES_JSON ?? '[]' ?>;

            // ===== util =====
            const debounce = (fn, ms = 250) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...a), ms);
                };
            };
            const getDigits = (v) => String(v || '').replace(/\D+/g, '');
            const groupThousands = (n) => String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            const formatRupiahLive = (digits) => digits ? 'Rp' + groupThousands(digits) : '';
            const formatRupiahFinal = (digits) => digits ? ('Rp' + groupThousands(digits) + ',00') : '';

            document.addEventListener('DOMContentLoaded', () => {
                // modal hapus
                const modalEl = document.getElementById('modalHapus');
                if (modalEl) modalEl.addEventListener('show.bs.modal', e => {
                    const id = e.relatedTarget?.getAttribute('data-id') || '';
                    document.getElementById('hapus_id').value = id;
                });

                // anti double submit + normalisasi biaya
                document.querySelectorAll('form').forEach(f => {
                    f.addEventListener('submit', e => {
                        // normalisasi biaya → kirim digit murni
                        const biaya = f.querySelector('[name="total_biaya_proyek"][data-currency="rupiah"]');
                        if (biaya) biaya.value = getDigits(biaya.value);

                        // validasi terakhir
                        f.querySelectorAll('input,select,textarea').forEach(el => validateField(el));
                        if (f.querySelector('.is-invalid')) {
                            e.preventDefault();
                            e.stopPropagation();
                            return;
                        }

                        const btn = f.querySelector('button[type=submit]');
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

                // reset
                document.addEventListener('click', e => {
                    const b = e.target.closest('[data-reset-form]');
                    if (!b) return;
                    const form = b.closest('form');
                    if (!form) return;
                    form.reset();
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    const idEl = form.querySelector('[name="id_proyek"][data-prefix="PRJ"]');
                    if (idEl && !idEl.value) idEl.value = 'PRJ';
                });

                const form = document.querySelector('form.live-validate');
                if (!form) return;

                // kunci prefix PRJ
                const idInput = form.querySelector('[name="id_proyek"][data-prefix="PRJ"]');
                if (idInput) {
                    const PFX = 'PRJ';

                    // Normalisasi nilai → selalu "PRJ" + digit saja
                    const enforce = () => {
                        const digits = String(idInput.value || '').replace(/\D+/g, ''); // ambil angka saja
                        idInput.value = PFX + digits;
                        const pos = (PFX + digits).length;
                        idInput.setSelectionRange(pos, pos);
                    };

                    // init
                    if (!idInput.value || !idInput.value.startsWith(PFX)) idInput.value = PFX;
                    enforce();

                    idInput.addEventListener('keydown', (ev) => {
                        const start = idInput.selectionStart ?? 0;
                        const end = idInput.selectionEnd ?? start;
                        const suffix = (idInput.value || '').slice(PFX.length);
                        const suffixEmpty = suffix.length === 0;

                        // izinkan navigasi/shortcut
                        const navKeys = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'];
                        if (navKeys.includes(ev.key) || ev.ctrlKey || ev.metaKey || ev.altKey) return;

                        // jika caret masuk area prefix, geser ke ujung prefix
                        if (start < PFX.length) {
                            idInput.setSelectionRange(PFX.length, PFX.length);
                        }

                        // hanya angka yang boleh diketik
                        if (ev.key.length === 1 && !/[0-9]/.test(ev.key)) {
                            ev.preventDefault();
                            return;
                        }

                        // cegah menghapus prefix
                        // - backspace saat caret ≤ panjang prefix TANPA seleksi
                        if (ev.key === 'Backspace' && start <= PFX.length && end <= PFX.length) {
                            ev.preventDefault();
                            return;
                        }
                        // - backspace tepat setelah prefix ketika belum ada suffix
                        if (ev.key === 'Backspace' && start === PFX.length && end === PFX.length && suffixEmpty) {
                            ev.preventDefault();
                            return;
                        }
                        // - delete jika caret sebelum prefix/sedang menyeleksi prefix
                        if (ev.key === 'Delete' && start < PFX.length) {
                            ev.preventDefault();
                            return;
                        }
                    });

                    // setiap perubahan, sanitasi lagi (buang non-digit & jaga prefix)
                    idInput.addEventListener('input', enforce);

                    // paste: ambil digit-nya saja, tempel setelah prefix
                    idInput.addEventListener('paste', (ev) => {
                        ev.preventDefault();
                        let text = (ev.clipboardData || window.clipboardData).getData('text') || '';
                        text = text.replace(/^prj/i, ''); // buang PRJ kalau user paste lengkap
                        text = text.replace(/\D+/g, ''); // hanya angka
                        const current = idInput.value || PFX;
                        const selStart = Math.max(PFX.length, idInput.selectionStart ?? PFX.length);
                        const selEnd = Math.max(PFX.length, idInput.selectionEnd ?? PFX.length);
                        idInput.value = current.slice(0, selStart) + text + current.slice(selEnd);
                        enforce();
                    });
                }

                // format rupiah: saat ketik → Rp12.000 ; saat blur → Rp12.000,00
                const rp = form.querySelector('[name="total_biaya_proyek"][data-currency="rupiah"]');
                if (rp) {
                    const applyLive = () => {
                        const digits = getDigits(rp.value);
                        rp.value = formatRupiahLive(digits);
                    };
                    rp.addEventListener('input', debounce(applyLive, 30));
                    rp.addEventListener('focus', applyLive);
                    rp.addEventListener('blur', () => {
                        const digits = getDigits(rp.value);
                        rp.value = formatRupiahFinal(digits);
                    });
                    // normalize nilai awal (kalau ada)
                    if (rp.value) rp.value = formatRupiahLive(getDigits(rp.value));
                }

                form.querySelectorAll('input,select,textarea').forEach(el => {
                    el.addEventListener('input', debounce(() => validateField(el), 200));
                    el.addEventListener('blur', () => validateField(el));
                    el.addEventListener('change', () => validateField(el));
                });

                function setInvalid(el, msg) {
                    el.classList.add('is-invalid');
                    const fb = el.parentElement.querySelector('.invalid-feedback');
                    if (fb && msg) fb.textContent = msg;
                }

                function setValid(el) {
                    el.classList.remove('is-invalid');
                }

                function validateField(el) {
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
            });
        </script>

    </div>
</div>