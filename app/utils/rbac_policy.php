<?php
function rbac_category_names(): array
{
    return [1 => 'Operasional', 2 => 'Data Master', 3 => 'Pengaturan'];
}

function rbac_whitelist_urls(string $roleId): array
{
    return match ($roleId) {
        'RL001' => ['/dashboard', '/proyek', '/progres', '/pembayaran', '/karyawan', '/klien', '/brand', '/tahapan'],
        'RL002' => ['/dashboard', '/pembayaran', '/penjadwalan', '/tahapan-approval'],
        'RL003' => ['/dashboard', '/tahapan-aktif'],
        default => ['/dashboard'],
    };
}

function rbac_overlay_menus(string $roleId): array
{
    $common = [['id_menu' => null, 'nama_menu' => 'Dashboard', 'url' => '/dashboard', 'cat_id' => 1],];
    $admin  = array_merge($common, [
        ['id_menu' => null, 'nama_menu' => 'Manajemen Proyek', 'url' => '/proyek', 'cat_id' => 1],
        ['id_menu' => null, 'nama_menu' => 'Progres Proyek', 'url' => '/progres', 'cat_id' => 1],
        ['id_menu' => null, 'nama_menu' => 'Pembayaran', 'url' => '/pembayaran', 'cat_id' => 1],
        ['id_menu' => null, 'nama_menu' => 'Data Karyawan', 'url' => '/karyawan', 'cat_id' => 2],
        ['id_menu' => null, 'nama_menu' => 'Data Klien', 'url' => '/klien', 'cat_id' => 2],
        ['id_menu' => null, 'nama_menu' => 'Data Brand', 'url' => '/brand', 'cat_id' => 2],
        ['id_menu' => null, 'nama_menu' => 'Daftar Tahapan Proyek', 'url' => '/tahapan', 'cat_id' => 2],
    ]);
    $pm = array_merge($common, [
        ['id_menu' => null, 'nama_menu' => 'Pembayaran', 'url' => '/pembayaran', 'cat_id' => 1],
        ['id_menu' => null, 'nama_menu' => 'Jadwal Proyek', 'url' => '/penjadwalan', 'cat_id' => 1],
        ['id_menu' => null, 'nama_menu' => 'Persetujuan Tahapan', 'url' => '/tahapan-approval', 'cat_id' => 1],
    ]);
    $mandor = array_merge($common, [
        ['id_menu' => null, 'nama_menu' => 'Tahapan Proyek', 'url' => '/tahapan-aktif', 'cat_id' => 1],
    ]);
    return match ($roleId) {
        'RL001' => $admin,
        'RL002' => $pm,
        'RL003' => $mandor,
        default => $common,
    };
}

function rbac_menu_order(string $roleId): array
{
    return match ($roleId) {
        'RL001' => ['/dashboard' => 0, '/proyek' => 10, '/progres' => 20, '/pembayaran' => 25, '/karyawan' => 30, '/klien' => 40, '/brand' => 50, '/tahapan' => 60],
        'RL002' => ['/dashboard' => 0, '/pembayaran' => 10, '/penjadwalan' => 20, '/tahapan-approval' => 25],
        'RL003' => ['/dashboard' => 0, '/tahapan-aktif' => 10],
        default => ['/dashboard' => 0],
    };
}

function rbac_apply_policy(array $dbMenus, string $roleId): array
{
    $whitelist = rbac_whitelist_urls($roleId);
    $overlay   = rbac_overlay_menus($roleId);
    $order     = rbac_menu_order($roleId);
    $byUrl = [];
    foreach ($dbMenus as $m) {
        $url = $m['url'] ?? '';
        if ($url && in_array($url, $whitelist, true)) {
            $byUrl[$url] = [
                'id_menu' => $m['id_menu'] ?? null,
                'nama_menu' => $m['nama_menu'] ?? 'Menu',
                'url' => $url,
                'cat_id' => (int)($m['cat_id'] ?? ($m['kategori_menus_id_kategori_menus'] ?? 1)),
            ];
        }
    }
    foreach ($overlay as $o) {
        $byUrl[$o['url']] = $o;
    }
    uasort($byUrl, function ($a, $b) use ($order) {
        $wa = $order[$a['url']] ?? (1000 + ($a['cat_id'] * 10));
        $wb = $order[$b['url']] ?? (1000 + ($b['cat_id'] * 10));
        if ($wa === $wb) return ($a['cat_id'] === $b['cat_id']) ? strcasecmp($a['nama_menu'], $b['nama_menu']) : ($a['cat_id'] <=> $b['cat_id']);
        return $wa <=> $wb;
    });
    return array_values($byUrl);
}
