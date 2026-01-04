<?php
// app/partials/sidebar.php

$menus   = $_SESSION['user']['menus'] ?? [];
$catName = $_SESSION['user']['menu_cats'] ?? [];

/**
 * Normalisasi URL menu agar konsisten:
 * - selalu diawali '/'
 * - alias khusus: tahapan_aktif -> tahapan-aktif
 */
if (!function_exists('normalize_menu_url')) {
    function normalize_menu_url(string $url): string
    {
        $u = trim($url);
        if ($u === '') return '';
        if ($u[0] !== '/') $u = '/' . $u;

        // alias khusus (jaga kompatibilitas data lama)
        if ($u === '/tahapan_aktif') $u = '/tahapan-aktif';

        return $u;
    }
}

if (!function_exists('menu_href')) {
    function menu_href(string $base, string $url): string
    {
        $u = normalize_menu_url($url);
        return rtrim($base, '/') . '/index.php?r=' . ltrim($u, '/');
    }
}

// 1) buang menu BRAND (karena modul & tabel sudah tidak ada)
$menus = array_values(array_filter($menus, function ($m) {
    $u = normalize_menu_url((string)($m['url'] ?? ''));
    return $u !== '/brand';
}));

// Ambil dashboard agar selalu di atas
$dashboardItem = null;
foreach ($menus as $i => $m) {
    if (normalize_menu_url((string)($m['url'] ?? '')) === '/dashboard') {
        $dashboardItem = $m;
        unset($menus[$i]);
        break;
    }
}

// Kelompokkan menu per kategori
$byCat = [];
foreach ($menus as $m) {
    $catId = (int)($m['cat_id'] ?? 1);
    $byCat[$catId][] = $m;
}
ksort($byCat);

// Current route slug
$currentSlug = strtolower(trim($_GET['r'] ?? 'dashboard'));
if ($currentSlug === 'tahapan_aktif') $currentSlug = 'tahapan-aktif'; // alias
$currentUrl  = '/' . ltrim($currentSlug, '/');

$base = rtrim($BASE_URL, '/');

$iconMap = [
    '/dashboard'     => 'home-outline',
    '/proyek'        => 'briefcase-outline',
    '/progres'       => 'trending-up-outline',
    '/pembayaran'    => 'cash-outline',
    '/penjadwalan'   => 'calendar-outline',
    '/karyawan'      => 'person-outline',
    '/klien'         => 'people-outline',
    // '/brand' dihapus
    '/tahapan'       => 'list-outline',
    '/tahapan-aktif' => 'list-outline',
    '/roles'         => 'flag-outline',
];

$catAlias = [1 => 'Operasional', 2 => 'Data Master'];
?>
<aside class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div><img src="<?= $BASE_URL ?>assets/images/logo-icon.png" class="logo-icon" alt="logo icon"></div>
        <div>
            <h1 class="logo-text">
                <span class="logo-name">CV Graha Raya</span>
                <span class="logo-sub">Consultant</span>
            </h1>
        </div>
    </div>

    <ul class="metismenu" id="menu">
        <?php
        $dashUrl    = '/dashboard';
        $dashIcon   = $iconMap[$dashUrl] ?? 'home-outline';
        $dashName   = $dashboardItem['nama_menu'] ?? 'Dashboard';
        $dashActive = ($currentUrl === $dashUrl) || ($currentUrl === '/' && $dashUrl === '/dashboard');
        ?>
        <li class="<?= $dashActive ? 'mm-active' : '' ?>">
            <a href="<?= menu_href($base, $dashUrl) ?>">
                <div class="parent-icon"><ion-icon name="<?= $dashIcon ?>"></ion-icon></div>
                <div class="menu-title"><?= htmlspecialchars($dashName) ?></div>
            </a>
        </li>

        <?php if (!empty($byCat)): ?>
            <?php foreach ($byCat as $catId => $items): if (empty($items)) continue; ?>
                <li class="menu-label"><?= htmlspecialchars($catAlias[$catId] ?? ($catName[$catId] ?? 'Menu')) ?></li>

                <?php foreach ($items as $m):
                    $urlRaw  = (string)($m['url'] ?? '');
                    $url     = normalize_menu_url($urlRaw);
                    if ($url === '') continue;

                    // jika masih ada /brand nyasar (double safety)
                    if ($url === '/brand') continue;

                    $name     = (string)($m['nama_menu'] ?? 'Menu');
                    $isActive = ($currentUrl === $url);
                    $icon     = $iconMap[$url] ?? 'chevron-forward-outline';
                ?>
                    <li class="<?= $isActive ? 'mm-active' : '' ?>">
                        <a href="<?= menu_href($base, $url) ?>">
                            <div class="parent-icon"><ion-icon name="<?= $icon ?>"></ion-icon></div>
                            <div class="menu-title"><?= htmlspecialchars($name) ?></div>
                        </a>
                    </li>
                <?php endforeach; ?>

            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</aside>