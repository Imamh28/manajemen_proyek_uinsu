<?php
$menus   = $_SESSION['user']['menus'] ?? [];
$catName = $_SESSION['user']['menu_cats'] ?? [];

$dashboardItem = null;
foreach ($menus as $i => $m) {
    if (($m['url'] ?? '') === '/dashboard') {
        $dashboardItem = $m;
        unset($menus[$i]);
        break;
    }
}

$byCat = [];
foreach ($menus as $m) $byCat[(int)($m['cat_id'] ?? 1)][] = $m;
ksort($byCat);

$currentSlug = strtolower($_GET['r'] ?? 'dashboard');
$currentSlug = ($currentSlug === 'tahapan_aktif') ? 'tahapan-aktif' : $currentSlug; // alias
$currentUrl  = '/' . ltrim($currentSlug, '/');

$base = rtrim($BASE_URL, '/');

if (!function_exists('menu_href')) {
    function menu_href(string $base, string $url): string
    {
        return $base . '/index.php?r=' . ltrim($url, '/');
    }
}

$iconMap = [
    '/dashboard'     => 'home-outline',
    '/proyek'        => 'briefcase-outline',
    '/progres'       => 'trending-up-outline',
    '/pembayaran'    => 'cash-outline',
    '/penjadwalan'   => 'calendar-outline',
    '/karyawan'      => 'person-outline',
    '/klien'         => 'people-outline',
    '/brand'         => 'pricetags-outline',
    '/tahapan'       => 'list-outline',
    '/tahapan-aktif' => 'list-outline', // hyphen
    '/roles'         => 'flag-outline',
];

$catAlias = [1 => 'Operasional', 2 => 'Data Master'];
?>
<aside class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div><img src="<?= $BASE_URL ?>assets/images/logo-icon-2.png" class="logo-icon" alt="logo icon"></div>
        <div>
            <h1 class="logo-text">Manajemen</h1>
        </div>
    </div>
    <ul class="metismenu" id="menu">
        <?php
        $dashUrl   = '/dashboard';
        $dashIcon  = $iconMap[$dashUrl] ?? 'home-outline';
        $dashName  = $dashboardItem['nama_menu'] ?? 'Dashboard';
        $dashActive = ($currentUrl === $dashUrl) || ($currentUrl === '/' && $dashUrl === '/dashboard');
        ?>
        <li class="<?= $dashActive ? 'mm-active' : '' ?>">
            <a href="<?= menu_href($base, $dashUrl) ?>">
                <div class="parent-icon"><ion-icon name="<?= $dashIcon ?>"></ion-icon></div>
                <div class="menu-title"><?= htmlspecialchars($dashName) ?></div>
            </a>
        </li>

        <?php if (!empty($byCat)): foreach ($byCat as $catId => $items): if (empty($items)) continue; ?>
                <li class="menu-label"><?= htmlspecialchars($catAlias[$catId] ?? ($catName[$catId] ?? 'Menu')) ?></li>
                <?php foreach ($items as $m):
                    $url      = ($m['url'] === '/tahapan_aktif') ? '/tahapan-aktif' : $m['url']; // alias
                    $name     = $m['nama_menu'];
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
        <?php endforeach;
        endif; ?>
    </ul>
</aside>