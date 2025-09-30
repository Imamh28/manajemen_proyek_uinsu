<?php
// app/partials/navbar.php
require_once __DIR__ . '/../helpers/Notify.php';

$user    = $_SESSION['user'] ?? [];
$uid     = (string)($user['id'] ?? '');
$uName   = $user['nama_karyawan'] ?? $user['nama'] ?? $user['name'] ?? $user['username'] ?? 'Pengguna';
$roleId  = $user['role_id'] ?? '';
$roleStr = function_exists('role_name') ? role_name((string)$roleId) : ($user['role_name'] ?? 'User');

$UNREAD  = $uid ? notif_unread_count($GLOBALS['pdo'], $uid) : 0;
$NOTIFS  = $uid ? notif_latest($GLOBALS['pdo'], $uid, 8) : [];
?>
<!--start top header-->
<header class="top-header">
    <nav class="navbar navbar-expand gap-3">
        <div class="toggle-icon"><ion-icon name="menu-outline"></ion-icon></div>

        <div class="top-navbar-right ms-auto">
            <ul class="navbar-nav align-items-center">

                <li class="nav-item">
                    <a class="nav-link dark-mode-icon" href="javascript:;">
                        <div class="mode-icon"><ion-icon name="moon-outline"></ion-icon></div>
                    </a>
                </li>

                <!-- Notifications -->
                <li class="nav-item dropdown dropdown-large">
                    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="javascript:;" data-bs-toggle="dropdown">
                        <div class="position-relative">
                            <?php if ($UNREAD > 0): ?>
                                <span class="notify-badge"><?= (int)$UNREAD ?></span>
                            <?php endif; ?>
                            <ion-icon name="notifications-outline"></ion-icon>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="msg-header">
                            <p class="msg-header-title mb-0">Notifikasi</p>
                            <form method="POST" action="<?= $BASE_URL ?>index.php?r=notify/readall" class="ms-auto">
                                <?= function_exists('csrf_input') ? csrf_input() : '' ?>
                                <button class="btn btn-link p-0 msg-header-clear" type="submit">Tandai Semua Sudah Dibaca</button>
                            </form>
                        </div>

                        <div class="header-notifications-list">
                            <?php if (!empty($NOTIFS)): ?>
                                <?php foreach ($NOTIFS as $n):
                                    $t = strtolower($n['title'] ?? '');
                                    $cls = 'text-primary';
                                    $icon = 'notifications-outline';
                                    if (str_contains($t, 'hapus')) {
                                        $cls = 'text-danger';
                                        $icon = 'trash-outline';
                                    } elseif (str_contains($t, 'tolak') || str_contains($t, 'rejected')) {
                                        $cls = 'text-danger';
                                        $icon = 'close-circle-outline';
                                    } elseif (str_contains($t, 'setuju') || str_contains($t, 'approved')) {
                                        $cls = 'text-success';
                                        $icon = 'checkmark-done-outline';
                                    } elseif (str_contains($t, 'ubah') || str_contains($t, 'update')) {
                                        $cls = 'text-warning';
                                        $icon = 'create-outline';
                                    } elseif (str_contains($t, 'tinjau') || str_contains($t, 'pengajuan') || str_contains($t, 'tahapan')) {
                                        $cls = 'text-info';
                                        $icon = 'flag-outline';
                                    }
                                    $href = ($n['link'] ?? '') ?: 'javascript:;';
                                ?>
                                    <a class="dropdown-item" href="<?= $BASE_URL ?>index.php?r=notify/open&id=<?= (int)$n['id'] ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="notify <?= $cls ?>"><ion-icon name="<?= $icon ?>"></ion-icon></div>
                                            <div class="flex-grow-1">
                                                <h6 class="msg-name">
                                                    <?= htmlspecialchars($n['title']) ?>
                                                    <span class="msg-time float-end"><?= htmlspecialchars(date('d M H:i', strtotime($n['created_at']))) ?></span>
                                                </h6>
                                                <p class="msg-info"><?= htmlspecialchars($n['body']) ?></p>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted p-3">Tidak ada notifikasi.</div>
                            <?php endif; ?>
                        </div>
                        <a href="javascript:;">
                            <div class="text-center msg-footer">Semua Notifikasi</div>
                        </a>
                    </div>
                </li>

                <!-- User -->
                <li class="nav-item dropdown dropdown-user-setting">
                    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="javascript:;" data-bs-toggle="dropdown">
                        <div class="user-setting">
                            <img src="<?= $BASE_URL ?>assets/images/avatars/13.png" class="user-img" alt="">
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="javascript:;">
                                <div class="d-flex flex-row align-items-center gap-2">
                                    <img src="<?= $BASE_URL ?>assets/images/avatars/13.png" class="rounded-circle" width="54" height="54" alt="">
                                    <div>
                                        <h6 class="mb-0 dropdown-user-name"><?= htmlspecialchars($uName) ?></h6>
                                        <small class="mb-0 dropdown-user-designation text-secondary"><?= htmlspecialchars($roleStr) ?></small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:;">
                                <div class="d-flex align-items-center">
                                    <div><ion-icon name="person-outline"></ion-icon></div>
                                    <div class="ms-3"><span>Profile</span></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                <div class="d-flex align-items-center">
                                    <div><ion-icon name="log-out-outline"></ion-icon></div>
                                    <div class="ms-3"><span>Logout</span></div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </nav>
</header>
<!--end top header-->