<?php
// Variables from controller:
// $proyek, $klienList, $brandList, $karyawanList, $statusEnum, $BASE_URL,
// $EXISTING_NAMES_JSON, $CURRENT_NAME

$__updErr = $_SESSION['form_errors']['proyek_update'] ?? [];
$__updOld = $_SESSION['form_old']['proyek_update'] ?? [];
unset($_SESSION['form_errors']['proyek_update'], $_SESSION['form_old']['proyek_update']);

$val = function (string $k, string $def = '') use ($__updOld, $proyek) {
    return htmlspecialchars($__updOld[$k] ?? ($proyek[$k] ?? $def));
};
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
                    <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=proyek">Manajemen Proyek</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Proyek: <?= htmlspecialchars($proyek['nama_proyek']) ?></h5>
                <hr />
                <form class="row g-3 live-validate" novalidate method="POST" action="<?= $BASE_URL ?>index.php?r=proyek/update" enctype="multipart/form-data">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="id_proyek" value="<?= htmlspecialchars($proyek['id_proyek']) ?>">

                    <div class="col-md-6">
                        <label class="form-label">ID Proyek</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($proyek['id_proyek']) ?>" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Quotation</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($proyek['quotation'] ?? '') ?>" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nama Proyek</label>
                        <input type="text" name="nama_proyek" required data-maxlen="45" data-unique="nama"
                            data-current-name="<?= htmlspecialchars($CURRENT_NAME) ?>"
                            class="form-control <?= isset($__updErr['nama_proyek']) ? 'is-invalid' : '' ?>"
                            value="<?= $val('nama_proyek') ?>">
                        <div class="invalid-feedback"><?= $__updErr['nama_proyek'] ?? 'Wajib, unik, maksimal 45 karakter.' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Total Biaya Proyek</label>
                        <input type="text" name="total_biaya_proyek" required data-currency="rupiah"
                            class="form-control <?= isset($__updErr['total_biaya_proyek']) ? 'is-invalid' : '' ?>"
                            value="<?= $val('total_biaya_proyek') ?>">
                        <div class="invalid-feedback"><?= $__updErr['total_biaya_proyek'] ?? 'Wajib & harus angka.' ?></div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" required
                            class="form-control <?= isset($__updErr['deskripsi']) ? 'is-invalid' : '' ?>"><?= $val('deskripsi') ?></textarea>
                        <div class="invalid-feedback"><?= $__updErr['deskripsi'] ?? 'Wajib diisi.' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Alamat Proyek</label>
                        <input type="text" name="alamat" required
                            class="form-control <?= isset($__updErr['alamat']) ? 'is-invalid' : '' ?>"
                            value="<?= $val('alamat') ?>">
                        <div class="invalid-feedback"><?= $__updErr['alamat'] ?? 'Wajib diisi.' ?></div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" required
                            class="form-control <?= isset($__updErr['tanggal_mulai']) ? 'is-invalid' : '' ?>"
                            value="<?= $val('tanggal_mulai') ?>">
                        <div class="invalid-feedback"><?= $__updErr['tanggal_mulai'] ?? 'Tanggal wajib & valid.' ?></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" required
                            class="form-control <?= isset($__updErr['tanggal_selesai']) ? 'is-invalid' : '' ?>"
                            value="<?= $val('tanggal_selesai') ?>">
                        <div class="invalid-feedback"><?= $__updErr['tanggal_selesai'] ?? 'Tanggal wajib & valid.' ?></div>
                    </div>

                    <!-- Gambar kerja -->
                    <div class="col-md-6">
                        <label class="form-label">Gambar Kerja (opsional: ganti)</label>
                        <input type="file" name="gambar_kerja" accept=".jpg,.jpeg,.png,.heic"
                            class="form-control <?= isset($__updErr['gambar_kerja']) ? 'is-invalid' : '' ?>">
                        <div class="invalid-feedback"><?= $__updErr['gambar_kerja'] ?? 'Tipe file harus JPG/JPEG/PNG/HEIC.' ?></div>
                        <?php if (!empty($proyek['gambar_kerja'])): ?>
                            <?php
                            // Normalisasi path yang disimpan di DB
                            $rel = ltrim((string)$proyek['gambar_kerja'], '/');
                            $rel = preg_replace('#^public/#', '', $rel); // handle record lama

                            // __DIR__ saat ini: app/views/admin/proyek
                            // Naik ke app/ lalu tambahkan path relatif uploads/...
                            $abs = __DIR__ . '/../../../' . $rel;
                            $exists = is_file($abs);
                            ?>
                            <div class="form-text">
                                Saat ini:
                                <?php if ($exists): ?>
                                    <a href="<?= $BASE_URL . htmlspecialchars($rel) ?>" target="_blank" rel="noopener">Lihat file</a>
                                <?php else: ?>
                                    <span class="text-danger">
                                        File <code><?= htmlspecialchars(basename($rel)) ?></code> tidak ditemukan di server.
                                        Silakan unggah ulang bila diperlukan.
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" required class="form-select <?= isset($__updErr['status']) ? 'is-invalid' : '' ?>">
                            <?php foreach ($statusEnum as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>" <?= ($val('status') === htmlspecialchars($s)) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $__updErr['status'] ?? 'Status wajib dipilih.' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Klien</label>
                        <select name="klien_id_klien" required class="form-select <?= isset($__updErr['klien_id_klien']) ? 'is-invalid' : '' ?>">
                            <?php foreach ($klienList as $k): ?>
                                <option value="<?= $k['id_klien'] ?>" <?= ($val('klien_id_klien') == $k['id_klien']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_klien']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $__updErr['klien_id_klien'] ?? 'Klien wajib dipilih.' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Brand</label>
                        <select name="brand_id_brand" required class="form-select <?= isset($__updErr['brand_id_brand']) ? 'is-invalid' : '' ?>">
                            <?php foreach ($brandList as $b): ?>
                                <option value="<?= $b['id_brand'] ?>" <?= ($val('brand_id_brand') == $b['id_brand']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['nama_brand']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $__updErr['brand_id_brand'] ?? 'Brand wajib dipilih.' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">PIC Sales</label>
                        <select name="karyawan_id_pic_sales" required class="form-select <?= isset($__updErr['karyawan_id_pic_sales']) ? 'is-invalid' : '' ?>">
                            <?php foreach ($karyawanList as $k): ?>
                                <option value="<?= $k['id_karyawan'] ?>" <?= ($val('karyawan_id_pic_sales') == $k['id_karyawan']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_karyawan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $__updErr['karyawan_id_pic_sales'] ?? 'PIC Sales wajib.' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">PIC Site</label>
                        <select name="karyawan_id_pic_site" required class="form-select <?= isset($__updErr['karyawan_id_pic_site']) ? 'is-invalid' : '' ?>">
                            <?php foreach ($karyawanList as $k): ?>
                                <option value="<?= $k['id_karyawan'] ?>" <?= ($val('karyawan_id_pic_site') == $k['id_karyawan']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_karyawan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $__updErr['karyawan_id_pic_site'] ?? 'PIC Site wajib.' ?></div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary px-4">Update Proyek</button>
                        <a href="<?= $BASE_URL ?>index.php?r=proyek" class="btn btn-secondary px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>

        <script>
            window.PROJ_EXISTING_NAMES = <?= $EXISTING_NAMES_JSON ?? '[]' ?>;
            window.PROJ_CURRENT_NAME = "<?= htmlspecialchars($CURRENT_NAME) ?>";

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
                document.querySelectorAll('form').forEach(f => {
                    f.addEventListener('submit', e => {
                        f.querySelectorAll('input,select,textarea').forEach(el => validate(el));
                        if (f.querySelector('.is-invalid')) {
                            e.preventDefault();
                            return;
                        }
                        const biaya = f.querySelector('[name="total_biaya_proyek"][data-currency="rupiah"]');
                        if (biaya) biaya.value = getDigits(biaya.value);
                    });
                });

                const form = document.querySelector('form.live-validate');
                if (!form) return;

                const rp = form.querySelector('[name="total_biaya_proyek"][data-currency="rupiah"]');
                if (rp) {
                    const applyLive = () => {
                        const d = getDigits(rp.value);
                        rp.value = formatRupiahLive(d);
                    };
                    rp.addEventListener('input', debounce(applyLive, 30));
                    rp.addEventListener('focus', applyLive);
                    rp.addEventListener('blur', () => {
                        const d = getDigits(rp.value);
                        rp.value = formatRupiahFinal(d);
                    });
                    if (rp.value) rp.value = formatRupiahLive(getDigits(rp.value));
                }

                form.querySelectorAll('input,select,textarea').forEach(el => {
                    el.addEventListener('input', debounce(() => validate(el), 200));
                    el.addEventListener('blur', () => validate(el));
                    el.addEventListener('change', () => validate(el));
                });

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
            });
        </script>

    </div>
</div>