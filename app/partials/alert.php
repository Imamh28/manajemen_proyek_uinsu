<?php if (isset($_SESSION['success'])) : ?>
    <div class="alert alert-dismissible fade show py-2 bg-success">
        <div class="d-flex align-items-center">
            <div class="fs-3 text-white">
                <ion-icon name="checkmark-circle-sharp"></ion-icon>
            </div>
            <div class="ms-3">
                <div class="text-white"><?= $_SESSION['success']; ?></div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])) : ?>
    <div class="alert alert-dismissible fade show py-2 bg-danger">
        <div class="d-flex align-items-center">
            <div class="fs-3 text-white">
                <ion-icon name="close-circle-sharp"></ion-icon>
            </div>
            <div class="ms-3">
                <div class="text-white"><?= $_SESSION['error']; ?></div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>