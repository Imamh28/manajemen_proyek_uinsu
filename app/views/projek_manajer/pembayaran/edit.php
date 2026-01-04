<?php
// app/views/projek_manajer/pembayaran/edit.php
// from controller:
// $pembayaran, $proyekList, $jenisEnum, $BASE_URL, $__updErr, $__updOld, $PROJECT_META_JSON, $ONLY_PROJECT

$val = function ($k, $def = '') use ($__updOld, $pembayaran) {
    return htmlspecialchars($__updOld[$k] ?? ($pembayaran[$k] ?? $def));
};

$currPid = (string)($pembayaran['proyek_id_proyek'] ?? '');
$currTotal = (int)round((float)($pembayaran['total_pembayaran'] ?? 0));
$hasProjects = !empty($proyekList);
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

                        <?php if (!empty($ONLY_PROJECT)): ?>
                            <input type="hidden" name="proyek_id_proyek" value="<?= htmlspecialchars($ONLY_PROJECT['id_proyek']) ?>">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($ONLY_PROJECT['id_proyek'] . ' — ' . ($ONLY_PROJECT['nama_proyek'] ?? '')) ?>" readonly>
                        <?php else: ?>
                            <select name="proyek_id_proyek" required class="form-select <?= isset($__updErr['proyek_id_proyek']) ? 'is-invalid' : '' ?>">
                                <?php foreach ($proyekList as $p): ?>
                                    <option value="<?= $p['id_proyek'] ?>" <?= ($val('proyek_id_proyek') == $p['id_proyek']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['id_proyek'] . ' — ' . ($p['nama_proyek'] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?= $__updErr['proyek_id_proyek'] ?? 'Wajib dipilih.' ?></div>
                        <?php endif; ?>

                        <div class="form-text" id="prj_meta_hint"></div>
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
                        <div class="form-text" id="limit_hint"></div>
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

                    <div class="col-md-6">
                        <label class="form-label">Bukti Pembayaran (opsional: ganti)</label>
                        <input type="file" name="bukti_pembayaran" accept=".jpg,.jpeg,.png,.heic" class="form-control <?= isset($__updErr['bukti_pembayaran']) ? 'is-invalid' : '' ?>">
                        <div class="invalid-feedback"><?= $__updErr['bukti_pembayaran'] ?? 'JPG/JPEG/PNG/HEIC.' ?></div>

                        <?php if (!empty($pembayaran['bukti_pembayaran'])): ?>
                            <?php
                            $rel = ltrim((string)$pembayaran['bukti_pembayaran'], '/');
                            $rel = preg_replace('#^(?:app/)?(?:public/)?#', '', $rel);
                            $abs = __DIR__ . '/../../../' . $rel;
                            $exists = is_file($abs);
                            ?>
                            <div class="form-text">
                                Saat ini:
                                <?php if ($exists): ?>
                                    <a href="<?= $BASE_URL . htmlspecialchars($rel) ?>" target="_blank" rel="noopener">Lihat bukti</a>
                                <?php else: ?>
                                    <span class="text-danger">
                                        File <code><?= htmlspecialchars(basename($rel)) ?></code> tidak ditemukan. Silakan unggah ulang bila perlu.
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
            window.PAY_PROJECT_META = <?= $PROJECT_META_JSON ?? '{}' ?>;
            window.CURRENT_PAYMENT = {
                proyek_id: <?= json_encode($currPid, JSON_UNESCAPED_UNICODE) ?>,
                total: <?= (int)$currTotal ?>
            };

            const debounce = (fn, ms = 250) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...a), ms);
                };
            };

            const digitsOnly = s => (s || '').replace(/\D+/g, '');
            const fmtRp = d => d ? 'Rp' + d.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : 'Rp0';

            function maxSubFromRemaining(remainingTotal, taxRate = 0.10) {
                let sub = Math.floor(remainingTotal / (1.0 + taxRate));
                for (let i = 0; i < 5; i++) {
                    const pajak = Math.round(sub * taxRate);
                    const tot = sub + pajak;
                    if (tot <= remainingTotal) return Math.max(0, sub);
                    sub--;
                    if (sub <= 0) return 0;
                }
                return Math.max(0, sub);
            }

            document.addEventListener('DOMContentLoaded', () => {
                const form = document.querySelector('form.live-validate');
                if (!form) return;

                const proyekEl = form.querySelector('[name="proyek_id_proyek"]'); // select OR hidden
                const sub = form.querySelector('[name="sub_total"][data-currency="rupiah"]');
                const paj = form.querySelector('[name="pajak_pembayaran"]');
                const ttl = form.querySelector('[name="total_pembayaran"]');
                const prjHint = document.getElementById('prj_meta_hint');
                const limitHint = document.getElementById('limit_hint');

                function setInvalid(el, msg) {
                    if (!el) return;
                    el.classList.add('is-invalid');
                    const fb = el.parentElement?.querySelector('.invalid-feedback');
                    if (fb && msg) fb.textContent = msg;
                }

                function setValid(el) {
                    if (!el) return;
                    el.classList.remove('is-invalid');
                }

                function getProjectId() {
                    return (proyekEl?.value || '').trim();
                }

                function projectInfo(pid) {
                    const m = (window.PAY_PROJECT_META || {})[pid];
                    if (!m) return null;

                    const total = parseInt(m.total || 0, 10) || 0;
                    let paid = parseInt(m.paid || 0, 10) || 0;

                    if (window.CURRENT_PAYMENT && window.CURRENT_PAYMENT.proyek_id === pid) {
                        paid = Math.max(0, paid - (parseInt(window.CURRENT_PAYMENT.total || 0, 10) || 0));
                    }

                    const remaining = total - paid;
                    return {
                        total,
                        paid,
                        remaining
                    };
                }

                function updateHints() {
                    const pid = getProjectId();
                    const info = pid ? projectInfo(pid) : null;

                    if (prjHint) {
                        if (!pid) prjHint.textContent = '';
                        else if (!info) prjHint.textContent = 'Meta proyek tidak ditemukan.';
                        else {
                            prjHint.textContent =
                                `Total Proyek: ${fmtRp(String(info.total))} • Sudah Tercatat (exclude record ini): ${fmtRp(String(info.paid))} • Sisa: ${fmtRp(String(Math.max(0, info.remaining)))}`;
                        }
                    }
                }

                function validateRemaining() {
                    if (!sub) return;

                    const pid = getProjectId();
                    const info = pid ? projectInfo(pid) : null;
                    if (!pid || !info) {
                        if (limitHint) limitHint.textContent = '';
                        return;
                    }

                    const s = parseInt(digitsOnly(sub.value) || '0', 10) || 0;
                    const tax = Math.round(s * 0.10);
                    const tot = s + tax;

                    if (info.total <= 0) {
                        setInvalid(sub, 'Total biaya proyek belum diset / tidak valid.');
                        if (limitHint) limitHint.textContent = '';
                        return;
                    }

                    if (info.remaining <= 0) {
                        setInvalid(sub, `Proyek ini sudah mencapai total biaya. Sisa: ${fmtRp('0')}.`);
                        if (limitHint) limitHint.textContent = '';
                        return;
                    }

                    if (tot > info.remaining) {
                        const maxSub = maxSubFromRemaining(info.remaining, 0.10);
                        setInvalid(sub,
                            `Nominal melebihi sisa tagihan. Sisa (termasuk pajak): ${fmtRp(String(info.remaining))}. ` +
                            `Maks sub total (sebelum pajak 10%): ${fmtRp(String(maxSub))}.`
                        );
                        if (limitHint) limitHint.textContent = '';
                        return;
                    }

                    setValid(sub);

                    if (limitHint) {
                        const maxSub = maxSubFromRemaining(info.remaining, 0.10);
                        limitHint.textContent = `Maks Sub Total saat ini: ${fmtRp(String(maxSub))} (agar total + pajak tidak melebihi sisa).`;
                    }
                }

                const recalc = () => {
                    if (!sub) return;
                    const d = digitsOnly(sub.value);
                    sub.value = d ? ('Rp' + d.replace(/\B(?=(\d{3})+(?!\d))/g, '.')) : '';
                    const v = d ? parseInt(d, 10) : 0;
                    const tax = Math.round(v * 0.10);
                    const tot = v + tax;
                    if (paj) paj.value = tax ? ('Rp' + String(tax).replace(/\B(?=(\d{3})+(?!\d))/g, '.')) : '';
                    if (ttl) ttl.value = tot ? ('Rp' + String(tot).replace(/\B(?=(\d{3})+(?!\d))/g, '.')) : '';

                    updateHints();
                    validateRemaining();
                };

                if (sub) {
                    sub.addEventListener('input', debounce(recalc, 40));
                    sub.addEventListener('blur', recalc);
                    if (sub.value && !String(sub.value).startsWith('Rp')) {
                        const dd = digitsOnly(sub.value);
                        sub.value = dd ? ('Rp' + dd.replace(/\B(?=(\d{3})+(?!\d))/g, '.')) : '';
                    }
                    recalc();
                }

                if (proyekEl) {
                    proyekEl.addEventListener('change', () => {
                        updateHints();
                        validateRemaining();
                    });
                }

                form.querySelectorAll('input,select,textarea').forEach(el => {
                    el.addEventListener('input', debounce(() => validate(el), 200));
                    el.addEventListener('blur', () => validate(el));
                    el.addEventListener('change', () => validate(el));
                });

                function validate(el) {
                    const val = (el.value || '').trim();
                    let msg = '';

                    if (!msg && el.hasAttribute('required') && !val) msg = 'Wajib diisi.';

                    if (!msg && el.name === 'sub_total' && val) {
                        if (!digitsOnly(val)) msg = 'Sub total harus angka.';
                    }

                    if (!msg && el.type === 'file' && el.files && el.files.length) {
                        const ok = Array.from(el.files).every(f => {
                            const ext = (f.name.split('.').pop() || '').toLowerCase();
                            return ['jpg', 'jpeg', 'png', 'heic'].includes(ext);
                        });
                        if (!ok) msg = 'Bukti harus JPG/JPEG/PNG/HEIC.';
                    }

                    if (msg) setInvalid(el, msg);
                    else setValid(el);

                    if (el.name === 'sub_total' || el.name === 'proyek_id_proyek') {
                        validateRemaining();
                    }
                }

                form.addEventListener('submit', (e) => {
                    form.querySelectorAll('input,select,textarea').forEach(el => validate(el));
                    validateRemaining();

                    if (form.querySelector('.is-invalid')) {
                        e.preventDefault();
                        return;
                    }

                    ['sub_total', 'pajak_pembayaran', 'total_pembayaran'].forEach(n => {
                        const el = form.querySelector(`[name="${n}"]`);
                        if (el) el.value = digitsOnly(el.value);
                    });
                });

                updateHints();
                validateRemaining();
            });
        </script>

    </div>
</div>