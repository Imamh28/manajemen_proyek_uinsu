<?php
// views/partials/alert.php (toast/overlay, tidak mengganggu layout)

// Kumpulkan flash messages dari session → jadikan array toasts
$__toasts = [];
if (!empty($_SESSION['success'])) {
    $__toasts[] = ['type' => 'success', 'title' => 'Berhasil', 'icon' => 'checkmark-circle-sharp', 'msg' => $_SESSION['success']];
    unset($_SESSION['success']);
}
if (!empty($_SESSION['error'])) {
    $__toasts[] = ['type' => 'danger', 'title' => 'Gagal', 'icon' => 'close-circle-sharp', 'msg' => $_SESSION['error']];
    unset($_SESSION['error']);
}
// (opsional) dukung juga warning/info kalau nanti dipakai:
if (!empty($_SESSION['warning'])) {
    $__toasts[] = ['type' => 'warning', 'title' => 'Perhatian', 'icon' => 'warning-sharp', 'msg' => $_SESSION['warning']];
    unset($_SESSION['warning']);
}
if (!empty($_SESSION['info'])) {
    $__toasts[] = ['type' => 'info', 'title' => 'Info', 'icon' => 'information-circle-sharp', 'msg' => $_SESSION['info']];
    unset($_SESSION['info']);
}
?>

<?php if (!empty($__toasts)): ?>
    <!-- Container toast fixed di pojok kanan atas -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;">
        <?php foreach ($__toasts as $t): ?>
            <?php
            // mapping warna background Bootstrap
            $bg = 'bg-' . $t['type']; // success/danger/warning/info
            ?>
            <div class="toast align-items-center text-white <?= $bg ?> border-0 shadow rounded-3"
                role="alert" aria-live="assertive" aria-atomic="true"
                data-bs-autohide="true" data-bs-delay="4000"> <!-- auto hide 4 detik -->
                <div class="d-flex">
                    <div class="toast-body">
                        <div class="d-flex align-items-start">
                            <ion-icon name="<?= htmlspecialchars($t['icon']) ?>" class="me-2" style="font-size:1.25rem;"></ion-icon>
                            <div>
                                <div class="fw-semibold mb-1"><?= htmlspecialchars($t['title']) ?></div>
                                <div><?= htmlspecialchars($t['msg']) ?></div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Tampilkan semua toast saat halaman siap
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.toast').forEach(el => {
                const t = bootstrap.Toast.getOrCreateInstance(el);
                t.show();
            });
        });
    </script>
<?php endif; ?>