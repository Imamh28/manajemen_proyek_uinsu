<?php
// Variabel dari controller: $brands, $BASE_URL, $EXISTING_BRAND_NAMES_JSON
$__storeErrors = $_SESSION['form_errors']['brand_store'] ?? [];
$__storeOld    = $_SESSION['form_old']['brand_store']    ?? [];
unset($_SESSION['form_errors']['brand_store'], $_SESSION['form_old']['brand_store']);
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
                        <li class="breadcrumb-item active" aria-current="page">Data Brand</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- FORM TAMBAH -->
        <form class="row g-3 live-validate" novalidate action="<?= $BASE_URL ?>index.php?r=brand/store" method="POST">
            <?= csrf_input(); ?>

            <div class="col-md-12">
                <label class="form-label">Nama Brand</label>
                <input type="text" name="nama_brand" required data-maxlen="45" data-unique="brand"
                    class="form-control <?= isset($__storeErrors['nama_brand']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($__storeOld['nama_brand'] ?? '') ?>">
                <div class="invalid-feedback">
                    <?= $__storeErrors['nama_brand'] ?? 'Nama brand wajib, maksimal 45 karakter, dan unik.' ?>
                </div>
            </div>

            <div class="col-12 mb-2">
                <button type="submit" class="btn btn-primary px-4">Tambah Brand</button>
                <button type="button" class="btn btn-outline-secondary ms-2" data-reset-form>Reset</button>
            </div>
        </form>

        <!-- spasi aman antara form & tabel -->
        <div class="my-3"></div>

        <!-- LIST -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h5 class="card-title">Daftar Brand</h5>
                    </div>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <form action="<?= $BASE_URL ?>index.php" method="GET" class="d-flex">
                            <input type="hidden" name="r" value="brand">
                            <input type="text" class="form-control" name="search" placeholder="Cari brand..."
                                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width:250px">
                            <button type="submit" class="btn btn-primary ms-2">Cari</button>
                        </form>
                        <a class="btn btn-outline-primary"
                            href="<?= $BASE_URL ?>index.php?r=brand/export&search=<?= urlencode($_GET['search'] ?? '') ?>">
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
                                <th>Nama Brand</th>
                                <th style="width:110px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($brands)): foreach ($brands as $b): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($b['id_brand']) ?></td>
                                        <td><?= htmlspecialchars($b['nama_brand']) ?></td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <a class="ms-3" href="<?= $BASE_URL ?>index.php?r=brand/edit&id=<?= urlencode($b['id_brand']) ?>">
                                                    <ion-icon name="create-outline"></ion-icon>
                                                </a>
                                                <a class="ms-3" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalHapus"
                                                    data-id="<?= htmlspecialchars($b['id_brand']) ?>">
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
                <form class="modal-content" method="POST" action="<?= $BASE_URL ?>index.php?r=brand/delete">
                    <?= csrf_input(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="hapus_id" id="hapus_id">
                        Hapus brand ini?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // modal hapus
            const modalEl = document.getElementById('modalHapus');
            if (modalEl) {
                modalEl.addEventListener('show.bs.modal', function(ev) {
                    const id = ev.relatedTarget ? ev.relatedTarget.getAttribute('data-id') : '';
                    document.getElementById('hapus_id').value = id || '';
                });
            }

            // daftar nama brand (sudah lowercase dari controller.index)
            window.EXISTING_BRAND_NAMES = <?= $EXISTING_BRAND_NAMES_JSON ?? '[]' ?>;

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

                const input = form.querySelector('input[name="nama_brand"]');
                const fb = input?.parentElement.querySelector('.invalid-feedback');

                function setInvalid(msg) {
                    input.classList.add('is-invalid');
                    if (fb) fb.textContent = msg || 'Tidak valid';
                }

                function setValid() {
                    input.classList.remove('is-invalid');
                }

                function check() {
                    const v = (input.value || '').trim();
                    if (!v) return setInvalid('Nama brand wajib diisi.');
                    if (v.length > 45) return setInvalid('Nama brand maksimal 45 karakter.');

                    const list = (window.EXISTING_BRAND_NAMES || []); // sudah lowercase
                    if (list.includes(v.toLowerCase())) return setInvalid('Nama brand sudah digunakan.');

                    return setValid();
                }

                // live check
                const run = debounce(check, 200);
                input.addEventListener('input', run);
                input.addEventListener('blur', check);

                // validate-all + anti double submit
                form.addEventListener('submit', (e) => {
                    check();
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
            })();
        </script>

    </div>
</div>