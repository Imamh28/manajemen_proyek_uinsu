<?php
// app/views/admin/pembayaran/main.php
// from controller:
// $pembayarans, $proyekList, $jenisEnum, $statusEnum, $BASE_URL, $EXISTING_IDS_JSON, $PROJECT_META_JSON, $ONLY_PROJECT

$__err = $_SESSION['form_errors']['pembayaran_store'] ?? [];
$__old = $_SESSION['form_old']['pembayaran_store'] ?? [];
unset($_SESSION['form_errors']['pembayaran_store'], $_SESSION['form_old']['pembayaran_store']);

$hasProjects = !empty($proyekList);
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
                        <li class="breadcrumb-item active">Data Pembayaran</li>
                    </ol>
                </nav>
            </div>
        </div>

        <?php if (!$hasProjects): ?>
            <div class="alert alert-warning">
                Anda belum memiliki proyek (PIC Sales) sehingga tidak bisa menambahkan pembayaran.
            </div>
        <?php endif; ?>

        <!-- FORM TAMBAH -->
        <form class="row g-3 live-validate" novalidate action="<?= $BASE_URL ?>index.php?r=pembayaran/store" method="POST" enctype="multipart/form-data">
            <?= csrf_input(); ?>

            <div class="col-md-4">
                <label class="form-label">ID Pembayaran</label>
                <input type="text" name="id_pem_bayaran" required data-unique="id" data-prefix="PAY"
                    class="form-control <?= isset($__err['id_pem_bayaran']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__old['id_pem_bayaran'] ?? 'PAY') ?>">
                <div class="invalid-feedback"><?= $__err['id_pem_bayaran'] ?? 'Wajib & diawali "PAY".' ?></div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Proyek</label>

                <?php if (!empty($ONLY_PROJECT)): ?>
                    <!-- jika hanya 1 proyek: auto -->
                    <input type="hidden" name="proyek_id_proyek" value="<?= htmlspecialchars($ONLY_PROJECT['id_proyek']) ?>">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($ONLY_PROJECT['id_proyek'] . ' — ' . ($ONLY_PROJECT['nama_proyek'] ?? '')) ?>" readonly>
                <?php else: ?>
                    <select name="proyek_id_proyek" <?= $hasProjects ? 'required' : '' ?>
                        class="form-select <?= isset($__err['proyek_id_proyek']) ? 'is-invalid' : '' ?>"
                        <?= $hasProjects ? '' : 'disabled' ?>>
                        <option value="" disabled <?= empty($__old['proyek_id_proyek']) ? 'selected' : '' ?>>Pilih Proyek...</option>
                        <?php foreach ($proyekList as $p): ?>
                            <option value="<?= $p['id_proyek'] ?>" <?= (($__old['proyek_id_proyek'] ?? '') == $p['id_proyek']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['id_proyek'] . ' — ' . ($p['nama_proyek'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"><?= $__err['proyek_id_proyek'] ?? 'Wajib dipilih.' ?></div>
                <?php endif; ?>

                <div class="form-text" id="prj_meta_hint"></div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Jenis Pembayaran</label>
                <select name="jenis_pembayaran" <?= $hasProjects ? 'required' : '' ?>
                    class="form-select <?= isset($__err['jenis_pembayaran']) ? 'is-invalid' : '' ?>"
                    <?= $hasProjects ? '' : 'disabled' ?>>
                    <option value="" disabled <?= empty($__old['jenis_pembayaran']) ? 'selected' : '' ?>>Pilih Jenis...</option>
                    <?php foreach ($jenisEnum as $j): ?>
                        <option value="<?= $j ?>" <?= (($__old['jenis_pembayaran'] ?? '') === $j) ? 'selected' : '' ?>><?= $j ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= $__err['jenis_pembayaran'] ?? 'Wajib dipilih.' ?></div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Sub Total</label>
                <input type="text" name="sub_total" <?= $hasProjects ? 'required' : '' ?> data-currency="rupiah"
                    class="form-control <?= isset($__err['sub_total']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__old['sub_total'] ?? '') ?>"
                    placeholder="Rp" <?= $hasProjects ? '' : 'disabled' ?>>
                <div class="invalid-feedback"><?= $__err['sub_total'] ?? 'Wajib & angka (Rp).' ?></div>
                <div class="form-text" id="limit_hint"></div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Pajak Pembayaran (10%)</label>
                <input type="text" name="pajak_pembayaran" readonly data-calc="true" class="form-control"
                    value="<?= htmlspecialchars($__old['pajak_pembayaran'] ?? '') ?>" placeholder="Rp">
            </div>

            <div class="col-md-4">
                <label class="form-label">Total Pembayaran</label>
                <input type="text" name="total_pembayaran" readonly data-calc="true" class="form-control"
                    value="<?= htmlspecialchars($__old['total_pembayaran'] ?? '') ?>" placeholder="Rp">
            </div>

            <div class="col-md-4">
                <label class="form-label">Tanggal Jatuh Tempo</label>
                <input type="date" name="tanggal_jatuh_tempo" <?= $hasProjects ? 'required' : '' ?>
                    class="form-control <?= isset($__err['tanggal_jatuh_tempo']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__old['tanggal_jatuh_tempo'] ?? '') ?>" <?= $hasProjects ? '' : 'disabled' ?>>
                <div class="invalid-feedback"><?= $__err['tanggal_jatuh_tempo'] ?? 'Wajib & valid.' ?></div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Tanggal Bayar</label>
                <input type="date" name="tanggal_bayar" <?= $hasProjects ? 'required' : '' ?>
                    class="form-control <?= isset($__err['tanggal_bayar']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__old['tanggal_bayar'] ?? '') ?>" <?= $hasProjects ? '' : 'disabled' ?>>
                <div class="invalid-feedback"><?= $__err['tanggal_bayar'] ?? 'Wajib & valid.' ?></div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status_pembayaran" <?= $hasProjects ? 'required' : '' ?>
                    class="form-select <?= isset($__err['status_pembayaran']) ? 'is-invalid' : '' ?>"
                    <?= $hasProjects ? '' : 'disabled' ?>>
                    <option value="" disabled <?= empty($__old['status_pembayaran']) ? 'selected' : '' ?>>Pilih Status...</option>
                    <?php foreach ($statusEnum as $s): ?>
                        <option value="<?= $s ?>" <?= (($__old['status_pembayaran'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= $__err['status_pembayaran'] ?? 'Wajib dipilih.' ?></div>
            </div>

            <div class="col-md-8">
                <label class="form-label">Bukti Pembayaran</label>
                <input type="file" name="bukti_pembayaran" <?= $hasProjects ? 'required' : '' ?> accept=".jpg,.jpeg,.png,.heic"
                    class="form-control <?= isset($__err['bukti_pembayaran']) ? 'is-invalid' : '' ?>" <?= $hasProjects ? '' : 'disabled' ?>>
                <div class="invalid-feedback"><?= $__err['bukti_pembayaran'] ?? 'Wajib, JPG/JPEG/PNG/HEIC.' ?></div>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <div class="ms-auto">
                    <button class="btn btn-primary px-4" <?= $hasProjects ? '' : 'disabled' ?>>Tambah Pembayaran</button>
                    <button type="button" class="btn btn-outline-secondary ms-2" data-reset-form <?= $hasProjects ? '' : 'disabled' ?>>Reset</button>
                </div>
            </div>
        </form>

        <div class="my-3"></div>

        <!-- LIST -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0">Daftar Pembayaran</h5>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <form action="<?= $BASE_URL ?>index.php" method="GET" class="d-flex">
                            <input type="hidden" name="r" value="pembayaran">
                            <input type="text" class="form-control" name="search" placeholder="Cari..."
                                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width:250px">
                            <button class="btn btn-primary ms-2">Cari</button>
                        </form>
                        <a class="btn btn-outline-primary"
                            href="<?= $BASE_URL ?>index.php?r=pembayaran/export&search=<?= urlencode($_GET['search'] ?? '') ?>">Export CSV</a>
                    </div>
                </div>
                <hr />
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Proyek</th>
                                <th>Jenis</th>
                                <th>Sub Total</th>
                                <th>Pajak</th>
                                <th>Total</th>
                                <th>Jatuh Tempo</th>
                                <th>Bayar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pembayarans)): foreach ($pembayarans as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['id_pem_bayaran']) ?></td>
                                        <td><?= htmlspecialchars(($r['proyek_id_proyek'] ?? '') . ' — ' . ($r['nama_proyek'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars($r['jenis_pembayaran']) ?></td>
                                        <td><?= 'Rp' . number_format((float)$r['sub_total'], 0, ',', '.') ?></td>
                                        <td><?= 'Rp' . number_format((float)$r['pajak_pembayaran'], 0, ',', '.') ?></td>
                                        <td><?= 'Rp' . number_format((float)$r['total_pembayaran'], 0, ',', '.') ?></td>
                                        <td><?= htmlspecialchars($r['tanggal_jatuh_tempo']) ?></td>
                                        <td><?= htmlspecialchars($r['tanggal_bayar']) ?></td>
                                        <td><span class="badge bg-<?= ($r['status_pembayaran'] === 'Lunas') ? 'success' : 'warning' ?>">
                                                <?= htmlspecialchars($r['status_pembayaran']) ?></span></td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <a class="ms-3" href="<?= $BASE_URL ?>index.php?r=pembayaran/edit&id=<?= urlencode($r['id_pem_bayaran']) ?>">
                                                    <ion-icon name="create-outline"></ion-icon>
                                                </a>
                                                <a class="ms-3" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                    data-id="<?= htmlspecialchars($r['id_pem_bayaran']) ?>">
                                                    <ion-icon name="trash-outline"></ion-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">Belum ada data.</td>
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
                <form class="modal-content" method="POST" action="<?= $BASE_URL ?>index.php?r=pembayaran/delete">
                    <?= csrf_input(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="hapus_id" id="hapus_id">
                        Hapus pembayaran ini?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-danger" type="submit">Hapus</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            window.PAY_EXISTING_IDS = <?= $EXISTING_IDS_JSON ?? '[]' ?>;
            window.PAY_PROJECT_META = <?= $PROJECT_META_JSON ?? '{}' ?>; // {PRJ..:{total,paid,name}}

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
                // modal isi id
                const modalEl = document.getElementById('modalHapus');
                if (modalEl) modalEl.addEventListener('show.bs.modal', e => {
                    document.getElementById('hapus_id').value = e.relatedTarget?.getAttribute('data-id') || '';
                });

                const form = document.querySelector('form.live-validate');
                if (!form) return;

                const payId = form.querySelector('[data-prefix="PAY"][name="id_pem_bayaran"]');
                const proyekEl = form.querySelector('[name="proyek_id_proyek"]'); // select OR hidden
                const sub = form.querySelector('[name="sub_total"][data-currency="rupiah"]');
                const paj = form.querySelector('[name="pajak_pembayaran"]');
                const ttl = form.querySelector('[name="total_pembayaran"]');

                const prjHint = document.getElementById('prj_meta_hint');
                const limitHint = document.getElementById('limit_hint');

                // kunci prefix PAY (create only)
                if (payId) {
                    const PFX = 'PAY';
                    const onlyDigits = s => String(s || '').replace(/\D+/g, '');

                    const enforce = () => {
                        const suffixDigits = onlyDigits(payId.value.slice(PFX.length));
                        payId.value = PFX + suffixDigits;
                        const pos = payId.value.length;
                        payId.setSelectionRange(pos, pos);
                    };

                    if (!payId.value || !payId.value.startsWith(PFX)) payId.value = PFX;
                    enforce();

                    payId.addEventListener('keydown', (ev) => {
                        const start = payId.selectionStart ?? 0;
                        const end = payId.selectionEnd ?? start;

                        const nav = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'];
                        if (nav.includes(ev.key) || ev.ctrlKey || ev.metaKey || ev.altKey) return;

                        if (start < PFX.length) {
                            payId.setSelectionRange(PFX.length, PFX.length);
                        }

                        if (ev.key.length === 1 && !/[0-9]/.test(ev.key)) {
                            ev.preventDefault();
                            return;
                        }

                        if (ev.key === 'Backspace' && start <= PFX.length && end <= PFX.length) {
                            ev.preventDefault();
                            return;
                        }
                        if (ev.key === 'Delete' && start < PFX.length) {
                            ev.preventDefault();
                            return;
                        }
                    });

                    payId.addEventListener('input', enforce);

                    payId.addEventListener('paste', (ev) => {
                        ev.preventDefault();
                        let text = (ev.clipboardData || window.clipboardData).getData('text') || '';
                        text = text.replace(/^pay/i, '');
                        text = text.replace(/\D+/g, '');

                        const cur = payId.value || PFX;
                        const selStart = Math.max(PFX.length, payId.selectionStart ?? PFX.length);
                        const selEnd = Math.max(PFX.length, payId.selectionEnd ?? PFX.length);
                        payId.value = cur.slice(0, selStart) + text + cur.slice(selEnd);
                        enforce();
                    });
                }

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
                    const paid = parseInt(m.paid || 0, 10) || 0;
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
                                `Total Proyek: ${fmtRp(String(info.total))} • Sudah Tercatat: ${fmtRp(String(info.paid))} • Sisa: ${fmtRp(String(Math.max(0, info.remaining)))}`;
                        }
                    }
                }

                function validateRemaining() {
                    if (!sub) return;

                    const pid = getProjectId();
                    const info = pid ? projectInfo(pid) : null;

                    // kalau belum pilih proyek (dan bukan hidden), biarkan required validator yang bekerja
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

                    // valid
                    if (sub.classList.contains('is-invalid')) {
                        // jangan hapus error lain jika ada, tapi untuk kasus ini aman
                        setValid(sub);
                    }

                    if (limitHint) {
                        const maxSub = maxSubFromRemaining(info.remaining, 0.10);
                        limitHint.textContent = `Maks Sub Total saat ini: ${fmtRp(String(maxSub))} (agar total + pajak tidak melebihi sisa).`;
                    }
                }

                // format & kalkulasi rupiah otomatis
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
                        sub.value = 'Rp' + digitsOnly(sub.value).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    }
                    recalc();
                }

                if (proyekEl) {
                    proyekEl.addEventListener('change', () => {
                        updateHints();
                        validateRemaining();
                    });
                }

                // reset
                document.addEventListener('click', e => {
                    const b = e.target.closest('[data-reset-form]');
                    if (!b) return;
                    form.reset();
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    if (payId) payId.value = 'PAY';
                    if (paj) paj.value = '';
                    if (ttl) ttl.value = '';
                    if (prjHint) prjHint.textContent = '';
                    if (limitHint) limitHint.textContent = '';
                });

                // live validate basic
                form.querySelectorAll('input,select,textarea').forEach(el => {
                    el.addEventListener('input', debounce(() => validate(el), 200));
                    el.addEventListener('blur', () => validate(el));
                    el.addEventListener('change', () => validate(el));
                });

                function validate(el) {
                    const val = (el.value || '').trim();
                    let msg = '';

                    if (!msg && el.hasAttribute('required') && !val) msg = 'Wajib diisi.';

                    if (!msg && el.name === 'id_pem_bayaran' && val) {
                        if (!val.startsWith('PAY')) msg = 'ID harus diawali "PAY".';
                        const list = (window.PAY_EXISTING_IDS || []).map(String);
                        if (!msg && list.includes(val)) msg = 'ID sudah digunakan.';
                    }

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

                    // ekstra: validasi batas sisa tagihan setiap kali sub_total / proyek berubah
                    if (el.name === 'sub_total' || el.name === 'proyek_id_proyek') {
                        validateRemaining();
                    }
                }

                // submit: normalisasi digits + final check remaining
                form.addEventListener('submit', (e) => {
                    form.querySelectorAll('input,select,textarea').forEach(el => validate(el));
                    validateRemaining();

                    if (form.querySelector('.is-invalid')) {
                        e.preventDefault();
                        e.stopPropagation();
                        return;
                    }

                    ['sub_total', 'pajak_pembayaran', 'total_pembayaran'].forEach(n => {
                        const el = form.querySelector(`[name="${n}"]`);
                        if (el) el.value = digitsOnly(el.value);
                    });

                    const btn = form.querySelector('button[type=submit]');
                    if (btn && btn.dataset.submitting === '1') {
                        e.preventDefault();
                        e.stopPropagation();
                        return;
                    }
                    if (btn) {
                        btn.dataset.submitting = '1';
                        btn.disabled = true;
                        btn.dataset.original = btn.innerHTML;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses…';
                    }
                });

                // init
                updateHints();
                validateRemaining();
            });
        </script>

    </div>
</div>