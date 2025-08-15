<?php

/**
 * Render reusable Bootstrap modal
 *
 * @param string $id        ID unik modal
 * @param string $title     Judul modal
 * @param string $message   Pesan isi modal
 * @param string $type      Warna background (primary, danger, success, warning, etc)
 * @param string $btnText   Teks tombol cancel
 * @param string $btnAction URL aksi tombol lanjut (jika kosong, tidak muncul)
 */
function renderModal($id, $title, $message, $type = 'primary', $btnText = 'Tutup', $btnAction = '')
{
?>
    <div class="modal fade" id="<?= $id ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-<?= $type ?>">
                <div class="modal-header">
                    <h5 class="modal-title text-white"><?= $title ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-white">
                    <?= $message ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= $btnText ?></button>
                    <?php if (!empty($btnAction)) : ?>
                        <a href="<?= $btnAction ?>" class="btn btn-dark">Lanjutkan</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>