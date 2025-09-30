<?php
// Variabel dari controller: $brand, $BASE_URL, $EXISTING_BRAND_NAMES_JSON
$__updErrors = $_SESSION['form_errors']['brand_update'] ?? [];
$__updOld    = $_SESSION['form_old']['brand_update']    ?? [];
unset($_SESSION['form_errors']['brand_update'], $_SESSION['form_old']['brand_update']);

$valNama = $__updOld['nama_brand'] ?? $brand['nama_brand'];
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
                        <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>index.php?r=brand">Data Brand</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Brand: <?= htmlspecialchars($brand['nama_brand']) ?></h5>
                <hr />
                <div class="p-4 border rounded">
                    <form class="row g-3 live-validate" novalidate method="POST" action="<?= $BASE_URL ?>index.php?r=brand/update">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="id_brand" value="<?= htmlspecialchars($brand['id_brand']) ?>">

                        <div class="col-md-12">
                            <label class="form-label">Nama Brand</label>
                            <input
                                type="text"
                                name="nama_brand"
                                required
                                data-maxlen="45"
                                data-unique="brand"
                                data-current-name="<?= htmlspecialchars(strtolower($brand['nama_brand'])) ?>"
                                class="form-control <?= isset($__updErrors['nama_brand']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($valNama) ?>">
                            <div class="invalid-feedback">
                                <?= $__updErrors['nama_brand'] ?? 'Nama brand wajib, maksimal 45 karakter, dan unik.' ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-4">Update Brand</button>
                            <a href="<?= $BASE_URL ?>index.php?r=brand" class="btn btn-secondary px-4">Batal</a>
                            <button type="button" class="btn btn-outline-secondary ms-2" data-reset-form>Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            // daftar nama brand untuk unique check (sudah lowercase dari controller.editForm)
            window.EXISTING_BRAND_NAMES = <?= $EXISTING_BRAND_NAMES_JSON ?? '[]' ?>;

            const debounce = (fn, wait = 250) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...a), wait);
                };
            };

            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('form.live-validate').forEach(form => {
                    const input = form.querySelector('input[name="nama_brand"]');
                    const fb = input?.parentElement.querySelector('.invalid-feedback');
                    const current = (input?.dataset.currentName || '').toLowerCase();

                    function setInvalid(msg) {
                        input.classList.add('is-invalid');
                        if (fb && msg) fb.textContent = msg;
                    }

                    function setValid() {
                        input.classList.remove('is-invalid');
                    }

                    function validate() {
                        const v = (input.value || '').trim();
                        if (!v) return setInvalid('Nama brand wajib diisi.');
                        if (v.length > 45) return setInvalid('Nama brand maksimal 45 karakter.');

                        const list = (window.EXISTING_BRAND_NAMES || []); // sudah lowercase
                        const vv = v.toLowerCase();
                        if (vv !== current && list.includes(vv)) return setInvalid('Nama brand sudah digunakan.');

                        return setValid();
                    }

                    // live
                    const run = debounce(validate, 200);
                    input.addEventListener('input', run);
                    input.addEventListener('blur', validate);
                    input.addEventListener('change', validate);

                    // submit guard
                    form.addEventListener('submit', (e) => {
                        validate();
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
                        setValid();
                    });
                });
            });
        </script>

    </div>
</div>