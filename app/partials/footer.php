<?php
$__app  = $APP_NAME ?? 'Manajemen Proyek';
$__year = date('Y');
?>
<!--start footer-->
<footer class="footer border-top">
    <div class="footer-text container-fluid py-2 small text-muted d-flex align-items-center justify-content-between">
        <span>© <?= $__year ?> <?= htmlspecialchars($__app) ?></span>
        <span class="d-none d-sm-inline">All rights reserved.</span>
    </div>
</footer>
<!--end footer-->