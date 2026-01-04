<?php
// app/utils/router.php

require_once __DIR__ . '/../utils/roles.php';
require_once __DIR__ . '/../middleware/authorize.php';

const DEFAULT_ROUTE = 'dashboard';

/**
 * Fallback kalau canon_url belum ada di authorize.php
 * Normalisasi URL menu menjadi format: /segmen[-segmen] tanpa trailing slash.
 */
if (!function_exists('canon_url')) {
    function canon_url(string $u): string
    {
        $u = trim($u);
        if ($u === '') return '/dashboard';

        // pastikan mulai dari slash
        if ($u[0] !== '/') $u = '/' . $u;

        // rapikan slash ganda
        $u = preg_replace('~/{2,}~', '/', $u);

        // hapus trailing slash kecuali root "/"
        if ($u !== '/' && str_ends_with($u, '/')) $u = rtrim($u, '/');

        return strtolower($u);
    }
}

/** Ambil path dari ?r= dan normalisasi */
function _resolve_path(): string
{
    $requested = $_GET['r'] ?? DEFAULT_ROUTE;

    // decode jika ada %2F, dsb
    $requested = urldecode((string)$requested);

    // sanitasi karakter (boleh a-z 0-9 / _ -)
    $requested = strtolower(preg_replace('~[^a-z0-9/_-]+~', '', $requested));
    $requested = trim($requested);

    if ($requested === '') $requested = DEFAULT_ROUTE;

    $path = '/' . ltrim($requested, '/');

    // rapikan slash ganda
    $path = preg_replace('~/{2,}~', '/', $path);

    // alias kompatibilitas route lama (underscore -> hyphen untuk route tertentu)
    $aliases = [
        '/tahapan_aktif'     => '/tahapan-aktif',
        '/tahapan_approval'  => '/tahapan-approval',
    ];
    foreach ($aliases as $from => $to) {
        if ($path === $from || str_starts_with($path, $from . '/')) {
            $path = $to . substr($path, strlen($from));
            break;
        }
    }

    // hapus trailing slash kecuali "/"
    if ($path !== '/' && str_ends_with($path, '/')) $path = rtrim($path, '/');

    // default "/" -> "/dashboard"
    if ($path === '/') $path = '/dashboard';

    return $path;
}

/** helper: cocok tepat /base atau /base/…  */
function is_route(string $path, string $base): bool
{
    return $path === $base || str_starts_with($path, $base . '/');
}

/**
 * Menu guard untuk sub-route (pakai segmen pertama):
 * /proyek/edit -> guard ke /proyek
 * /penjadwalan/detailproyek -> guard ke /penjadwalan
 */
function _guard_base(string $path): string
{
    if ($path === '/dashboard') return '/dashboard';

    $trim = trim($path, '/');
    if ($trim === '') return '/dashboard';

    $parts = explode('/', $trim);
    $first = $parts[0] ?? 'dashboard';

    return '/' . $first;
}

/** Seed menu_urls dari menus (kalau kosong), plus normalisasi & alias */
function _seed_menu_urls_from_menus(): void
{
    if (!isset($_SESSION['user'])) return;
    if (!empty($_SESSION['user']['menu_urls'])) return;

    $menus = $_SESSION['user']['menus'] ?? [];
    if (!$menus) return;

    $urls = [];
    foreach ($menus as $m) {
        if (!isset($m['url'])) continue;
        $urls[] = canon_url((string)$m['url']);
    }

    $urls = array_values(array_unique(array_filter($urls)));

    if (!in_array('/dashboard', $urls, true)) $urls[] = '/dashboard';

    // alias agar menu lama tetap kompatibel
    if (in_array('/tahapan_aktif', $urls, true) && !in_array('/tahapan-aktif', $urls, true)) {
        $urls[] = '/tahapan-aktif';
    }
    if (in_array('/tahapan_approval', $urls, true) && !in_array('/tahapan-approval', $urls, true)) {
        $urls[] = '/tahapan-approval';
    }

    $_SESSION['user']['menu_urls'] = $urls;
}

/**
 * Tangani AKSI (POST/GET tanpa layout)
 * Return true jika request sudah ditangani (controller sudah dipanggil / sudah redirect / sudah output).
 */
function handle_actions(string $BASE_URL): bool
{
    $path = _resolve_path();

    if (!isset($_SESSION['user'])) return false;

    _seed_menu_urls_from_menus();

    // === Karyawan
    if (is_route($path, '/karyawan')) {
        require_menu_access('/karyawan', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/KaryawanController.php';
        $c = new KaryawanController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/karyawan/store'  && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->store();
            return true;
        }
        if ($path === '/karyawan/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->update();
            return true;
        }
        if ($path === '/karyawan/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->delete();
            return true;
        }
        if ($path === '/karyawan/export' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->exportCSV();
            return true;
        }

        return false;
    }

    // === Klien
    if (is_route($path, '/klien')) {
        require_menu_access('/klien', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/KlienController.php';
        $c = new KlienController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/klien/store'  && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->store();
            return true;
        }
        if ($path === '/klien/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->update();
            return true;
        }
        if ($path === '/klien/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->delete();
            return true;
        }
        if ($path === '/klien/export' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->exportCSV();
            return true;
        }

        return false;
    }

    // === Tahapan (MASTER)
    if (is_route($path, '/tahapan')) {
        require_menu_access('/tahapan', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/TahapanController.php';
        $c = new TahapanController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/tahapan/store'  && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->store();
            return true;
        }
        if ($path === '/tahapan/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->update();
            return true;
        }
        if ($path === '/tahapan/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->delete();
            return true;
        }
        if ($path === '/tahapan/export' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->exportCSV();
            return true;
        }

        return false;
    }

    // === Proyek
    if (is_route($path, '/proyek')) {
        require_menu_access('/proyek', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/ProyekController.php';
        $c = new ProyekController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/proyek/store'      && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->store();
            return true;
        }
        if ($path === '/proyek/update'     && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->update();
            return true;
        }
        if ($path === '/proyek/delete'     && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->delete();
            return true;
        }
        if ($path === '/proyek/export'     && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->exportCSV();
            return true;
        }
        if ($path === '/proyek/set-status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->setStatus();
            return true;
        }

        return false;
    }

    // === Pembayaran
    if (is_route($path, '/pembayaran')) {
        require_menu_access('/pembayaran', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/PembayaranController.php';
        $c = new PembayaranController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/pembayaran/store'  && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->store();
            return true;
        }
        if ($path === '/pembayaran/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->update();
            return true;
        }
        if ($path === '/pembayaran/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->delete();
            return true;
        }
        if ($path === '/pembayaran/export' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->exportCSV();
            return true;
        }

        return false;
    }

    // === Penjadwalan
    if (is_route($path, '/penjadwalan')) {
        require_menu_access('/penjadwalan', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/PenjadwalanController.php';
        $c = new PenjadwalanController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/penjadwalan/store'  && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->store();
            return true;
        }
        if ($path === '/penjadwalan/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->update();
            return true;
        }
        if ($path === '/penjadwalan/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->delete();
            return true;
        }

        return false;
    }

    // === Tahapan AKTIF (MANDOR)
    if (is_route($path, '/tahapan-aktif')) {
        require_menu_access('/tahapan-aktif', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/TahapanAktifController.php';
        $c = new TahapanAktifController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/tahapan-aktif/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->update();
            return true;
        }

        // kalau kamu nanti punya endpoint file untuk bukti mandor, router siap (tidak fatal kalau method belum ada)
        if ($path === '/tahapan-aktif/file' && $_SERVER['REQUEST_METHOD'] === 'GET' && method_exists($c, 'file')) {
            $c->file();
            return true;
        }

        return false;
    }

    // === Persetujuan Tahapan (PM/Admin)
    if (is_route($path, '/tahapan-approval')) {
        require_menu_access('/tahapan-approval', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/TahapanApprovalController.php';
        $c = new TahapanApprovalController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/tahapan-approval/approve' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->approve();
            return true;
        }
        if ($path === '/tahapan-approval/reject'  && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->reject();
            return true;
        }

        if ($path === '/tahapan-approval/delete-request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->deleteRequest();
            return true;
        }

        // ✅ endpoint file: index.php?r=tahapan-approval/file&id=123&kind=foto|dokumen
        if ($path === '/tahapan-approval/file' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            if (method_exists($c, 'file')) {
                $c->file();
                return true;
            }
            // kalau method belum dibuat, biar jelas
            http_response_code(500);
            echo 'Method TahapanApprovalController::file() belum tersedia.';
            return true;
        }

        return false;
    }

    // === Notifications (open/readall)
    // NOTE: ini biasanya tidak ada di menu_urls, tapi aman karena dipanggil via handle_actions sebelum dispatch.
    if (is_route($path, '/notify')) {
        require_once __DIR__ . '/../controllers/NotificationController.php';
        $c = new NotificationController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/notify/readall' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $c->readAll();
            return true;
        }
        if ($path === '/notify/open'    && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->open();
            return true;
        }

        return false;
    }

    return false;
}

/**
 * Dispatch VIEW (GET)
 * Aman: jika index.php lupa memanggil handle_actions, dispatch_route juga akan coba menanganinya dulu.
 */
function dispatch_route(string $BASE_URL): void
{
    // kalau request action, handle dulu
    if (handle_actions($BASE_URL)) return;

    $path = _resolve_path();

    if (!isset($_SESSION['user'])) {
        header('Location: ' . $BASE_URL . 'auth/login.php');
        exit;
    }

    _seed_menu_urls_from_menus();

    // GUARD menu akses by base
    $guardUrl = _guard_base($path);
    require_menu_access($guardUrl, $BASE_URL);

    if (!empty($GLOBALS['__FORBIDDEN__'])) {
        $viewsRoot = __DIR__ . '/../views';
        $err403    = "$viewsRoot/error/403.php";
        if (is_file($err403)) include $err403;
        else {
            http_response_code(403);
            echo '<div class="container mt-5"><div class="alert alert-danger">403 — Akses ditolak.</div></div>';
        }
        return;
    }

    if ($path === '/dashboard') {
        $view = __DIR__ . '/../views/dashboard.php';
        if (is_file($view)) {
            include $view;
            return;
        }
    }

    // ====== Standard Controller Routes (VIEW) ======

    // Karyawan
    if (is_route($path, '/karyawan')) {
        require_once __DIR__ . '/../controllers/KaryawanController.php';
        $c = new KaryawanController($GLOBALS['pdo'], $BASE_URL);
        if ($path === '/karyawan'      && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->index();
            return;
        }
        if ($path === '/karyawan/edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->editForm();
            return;
        }
    }

    // Klien
    if (is_route($path, '/klien')) {
        require_once __DIR__ . '/../controllers/KlienController.php';
        $c = new KlienController($GLOBALS['pdo'], $BASE_URL);
        if ($path === '/klien'      && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->index();
            return;
        }
        if ($path === '/klien/edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->editForm();
            return;
        }
    }

    // Tahapan (MASTER)
    if (is_route($path, '/tahapan')) {
        require_once __DIR__ . '/../controllers/TahapanController.php';
        $c = new TahapanController($GLOBALS['pdo'], $BASE_URL);
        if ($path === '/tahapan'      && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->index();
            return;
        }
        if ($path === '/tahapan/edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->editForm();
            return;
        }
    }

    // Proyek
    if (is_route($path, '/proyek')) {
        require_once __DIR__ . '/../controllers/ProyekController.php';
        $c = new ProyekController($GLOBALS['pdo'], $BASE_URL);
        if ($path === '/proyek'      && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->index();
            return;
        }
        if ($path === '/proyek/edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->editForm();
            return;
        }
    }

    // Progres Proyek
    if (is_route($path, '/progres')) {
        require_once __DIR__ . '/../controllers/ProgresProyekController.php';
        $c = new ProgresProyekController($GLOBALS['pdo'], $BASE_URL);
        if ($path === '/progres' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->index();
            return;
        }
    }

    // Pembayaran
    if (is_route($path, '/pembayaran')) {
        require_once __DIR__ . '/../controllers/PembayaranController.php';
        $c = new PembayaranController($GLOBALS['pdo'], $BASE_URL);
        if ($path === '/pembayaran'      && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->index();
            return;
        }
        if ($path === '/pembayaran/edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->editForm();
            return;
        }
    }

    // Penjadwalan
    if (is_route($path, '/penjadwalan')) {
        require_once __DIR__ . '/../controllers/PenjadwalanController.php';
        $c = new PenjadwalanController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/penjadwalan'                  && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->index();
            return;
        }
        if ($path === '/penjadwalan/edit'             && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->editForm();
            return;
        }
        if ($path === '/penjadwalan/detailproyek'     && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->detailProyek();
            return;
        }
        if ($path === '/penjadwalan/detailpembayaran' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->detailPembayaran();
            return;
        }
    }

    // Tahapan AKTIF (Mandor)
    if (is_route($path, '/tahapan-aktif')) {
        require_once __DIR__ . '/../controllers/TahapanAktifController.php';
        $c = new TahapanAktifController($GLOBALS['pdo'], $BASE_URL);
        if ($path === '/tahapan-aktif' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->index();
            return;
        }
    }

    // Persetujuan Tahapan (PM/Admin)
    if (is_route($path, '/tahapan-approval')) {
        require_once __DIR__ . '/../controllers/TahapanApprovalController.php';
        $c = new TahapanApprovalController($GLOBALS['pdo'], $BASE_URL);
        if ($path === '/tahapan-approval' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->index();
            return;
        }
    }

    // ====== Fallback view by role ======
    $u = $_SESSION['user'] ?? [];
    $roleId  = (string)($u['role_id'] ?? ($u['role_id_role'] ?? ($u['role'] ?? ($u['id_role'] ?? ''))));
    $roleDir = role_dir($roleId);

    $viewsRoot = __DIR__ . '/../views';
    $view = null;

    if ($path === '/dashboard') {
        $c1 = "$viewsRoot/$roleDir/dashboard.php";
        $c2 = "$viewsRoot/dashboard.php";
        $view = is_file($c1) ? $c1 : (is_file($c2) ? $c2 : null);
    } else {
        $rel = trim($path, '/');
        $candidates = [
            "$viewsRoot/$roleDir/$rel.php",
            "$viewsRoot/$roleDir/$rel/index.php",
            "$viewsRoot/$rel.php",
            "$viewsRoot/$rel/index.php",
        ];
        foreach ($candidates as $c) {
            if (is_file($c)) {
                $view = $c;
                break;
            }
        }
    }

    if (!$view) {
        $err404 = "$viewsRoot/error/404.php";
        if (is_file($err404)) include $err404;
        else {
            http_response_code(404);
            echo '<div class="container mt-5"><div class="alert alert-warning">404 — Halaman tidak ditemukan.</div></div>';
        }
        return;
    }

    include $view;
}
