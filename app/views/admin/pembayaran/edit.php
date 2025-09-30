<?php
// from controller:
// $pembayaran, $proyekList, $jenisEnum, $statusEnum, $BASE_URL, $__updErr, $__updOld
$val = function ($k, $def = '') use ($__updOld, $pembayaran) {
    return htmlspecialchars($__updOld[$k] ?? ($pembayaran[$k] ?? $def));
};
?>
<div class="page-content-wrapper">
    <div class="page-content">

        <?php include __DIR__ . '/../../../partials/alert.php'; ?>

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operasional</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=dashboard"><ion-icon name="home-outline"></ion-icon></a></li>
                    <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=pembayaran">Data Pembayaran</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Pembayaran: <?= htmlspecialchars($pembayaran['id_pem_bayaran']) ?></h5>
                <hr />
                <form class="row g-3 live-validate" novalidate method="POST" action="<?= $BASE_URL ?>index.php?r=pembayaran/update" enctype="multipart/form-data">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="id_pem_bayaran" value="<?= htmlspecialchars($pembayaran['id_pem_bayaran']) ?>">

                    <div class="col-md-4">
                        <label class="form-label">ID Pembayaran</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($pembayaran['id_pem_bayaran']) ?>" disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Proyek</label>
                        <select name="proyek_id_proyek" required class="form-select <?= isset($__updErr['proyek_id_proyek']) ? 'is-invalid' : '' ?>">
                            <?php foreach ($proyekList as $p): ?>
                                <option value="<?= $p['id_proyek'] ?>" <?= ($val('proyek_id_proyek') == $p['id_proyek']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['id_proyek'] . ' — ' . $p['nama_proyek']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $__updErr['proyek_id_proyek'] ?? 'Wajib dipilih.' ?></div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Jenis Pembayaran</label>
                        <select name="jenis_pembayaran" required class="form-select <?= isset($__updErr['jenis_pembayaran']) ? 'is-invalid' : '' ?>">
                            <?php foreach ($jenisEnum as $j): ?>
                                <option value="<?= $j ?>" <?= ($val('jenis_pembayaran') === $j) ? 'selected' : '' ?>><?= $j ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $__updErr['jenis_pembayaran'] ?? 'Wajib dipilih.' ?></div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Sub Total</label>
                        <input type="text" name="sub_total" required data-currency="rupiah"
                            class="form-control <?= isset($__updErr['sub_total']) ? 'is-invalid' : '' ?>"
                            value="<?= $val('sub_total') ?>">
                        <div class="invalid-feedback"><?= $__updErr['sub_total'] ?? 'Wajib & angka.' ?></div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Pajak Pembayaran (10%)</label>
                        <input type="text" name="pajak_pembayaran" readonly data-calc="true" class="form-control" value="<?= $val('pajak_pembayaran') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Total Pembayaran</label>
                        <input type="text" name="total_pembayaran" readonly data-calc="true" class="form-control" value="<?= $val('total_pembayaran') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Jatuh Tempo</label>
                        <input type="date" name="tanggal_jatuh_tempo" required class="form-control <?= isset($__updErr['tanggal_jatuh_tempo']) ? 'is-invalid' : '' ?>" value="<?= $val('tanggal_jatuh_tempo') ?>">
                        <div class="invalid-feedback"><?= $__updErr['tanggal_jatuh_tempo'] ?? 'Wajib & valid.' ?></div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Bayar</label>
                        <input type="date" name="tanggal_bayar" required class="form-control <?= isset($__updErr['tanggal_bayar']) ? 'is-invalid' : '' ?>" value="<?= $val('tanggal_bayar') ?>">
                        <div class="invalid-feedback"><?= $__updErr['tanggal_bayar'] ?? 'Wajib & valid.' ?></div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status_pembayaran" required class="form-select <?= isset($__updErr['status_pembayaran']) ? 'is-invalid' : '' ?>">
                            <?php foreach ($statusEnum as $s): ?>
                                <option value="<?= $s ?>" <?= ($val('status_pembayaran') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $__updErr['status_pembayaran'] ?? 'Wajib dipilih.' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Bukti Pembayaran (opsional: ganti)</label>
                        <input type="file" name="bukti_pembayaran" accept=".jpg,.jpeg,.png,.heic" class="form-control <?= isset($__updErr['bukti_pembayaran']) ? 'is-invalid' : '' ?>">
                        <div class="invalid-feedback"><?= $__updErr['bukti_pembayaran'] ?? 'JPG/JPEG/PNG/HEIC.' ?></div>
                        <?php if (!empty($pembayaran['bukti_pembayaran'])): ?>
                            <?php
                            // Normalisasi path yang disimpan di DB (handle record lama yang mungkin menyimpan "app/" atau "public/")
                            $rel = ltrim((string)$pembayaran['bukti_pembayaran'], '/');
                            $rel = preg_replace('#^(?:app/)?(?:public/)?#', '', $rel); // jadikan "uploads/…"

                            // __DIR__ saat ini ada di: app/views/{role}/pembayaran
                            // Naik 3x → app/, lalu tambah path relatif uploads/…
                            $abs = __DIR__ . '/../../../' . $rel;
                            $exists = is_file($abs);
                            ?>
                            <div class="form-text">
                                Saat ini:
                                <?php if ($exists): ?>
                                    <a href="<?= $BASE_URL . htmlspecialchars($rel) ?>" target="_blank" rel="noopener">Lihat bukti</a>
                                <?php else: ?>
                                    <span class="text-danger">
                                        File <code><?= htmlspecialchars(basename($rel)) ?></code> tidak ditemukan di server.
                                        Silakan unggah ulang bila diperlukan.
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary px-4">Update Pembayaran</button>
                        <a href="<?= $BASE_URL ?>index.php?r=pembayaran" class="btn btn-secondary px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>

        <script>
            const debounce = (fn, ms = 250) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...a), ms);
                };
            };
            const digitsOnly = s => (s || '').replace(/\D+/g, '');
            const fmtRp = d => d ? 'Rp' + d.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';

            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('form').forEach(f => {
                    f.addEventListener('submit', e => {
                        f.querySelectorAll('input,select,textarea').forEach(el => validate(el));
                        if (f.querySelector('.is-invalid')) {
                            e.preventDefault();
                            return;
                        }
                        ['sub_total', 'pajak_pembayaran', 'total_pembayaran'].forEach(n => {
                            const el = f.querySelector(`[name="${n}"]`);
                            if (el) el.value = digitsOnly(el.value);
                        });
                    });
                });

                const form = document.querySelector('form.live-validate');
                if (!form) return;

                const sub = form.querySelector('[name="sub_total"][data-currency="rupiah"]');
                const paj = form.querySelector('[name="pajak_pembayaran"]');
                const ttl = form.querySelector('[name="total_pembayaran"]');

                const recalc = () => {
                    const d = digitsOnly(sub.value);
                    sub.value = fmtRp(d);
                    const v = d ? parseInt(d, 10) : 0;
                    const tax = Math.round(v * 0.10);
                    const tot = v + tax;
                    if (paj) paj.value = fmtRp(String(tax));
                    if (ttl) ttl.value = fmtRp(String(tot));
                };

                if (sub) {
                    sub.addEventListener('input', debounce(recalc, 40));
                    sub.addEventListener('blur', recalc);
                    if (sub.value && !sub.value.startsWith('Rp')) {
                        sub.value = fmtRp(digitsOnly(sub.value));
                        recalc();
                    }
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
                    const val = (el.value || '').trim();
                    let msg = '';
                    if (!msg && el.hasAttribute('required') && !val) msg = 'Wajib diisi.';
                    if (!msg && el.name === 'sub_total' && val) {
                        if (!digitsOnly(val)) msg = 'Sub total harus angka.';
                    }
                    if (!msg && el.type === 'file' && el.files && el.files.length) {
                        const ok = Array.from(el.files).every(f => ['jpg', 'jpeg', 'png', 'heic'].includes((f.name.split('.').pop() || '').toLowerCase()));
                        if (!ok) msg = 'Bukti harus JPG/JPEG/PNG/HEIC.';
                    }
                    if (msg) setInvalid(el, msg);
                    else setValid(el);
                }
            });
        </script>

    </div>
</div>