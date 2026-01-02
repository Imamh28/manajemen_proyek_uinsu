<?php
// app/utils/router.php

require_once __DIR__ . '/../utils/roles.php';
require_once __DIR__ . '/../middleware/authorize.php';

const DEFAULT_ROUTE = 'dashboard';

/** Ambil path dari ?r=  */
function _resolve_path(): string
{
    $requested = $_GET['r'] ?? DEFAULT_ROUTE;
    $requested = strtolower(preg_replace('~[^a-z0-9/_-]+~', '', $requested));
    $requested = $requested ?: DEFAULT_ROUTE;

    $path = '/' . ltrim($requested, '/');

    // alias: /tahapan_aktif -> /tahapan-aktif
    if ($path === '/tahapan_aktif' || str_starts_with($path, '/tahapan_aktif/')) {
        $path = str_replace('/tahapan_aktif', '/tahapan-aktif', $path);
    }

    return $path;
}

/** helper: cocok tepat /base atau /base/…  */
function is_route(string $path, string $base): bool
{
    return $path === $base || str_starts_with($path, $base . '/');
}

/**
 * Menu guard untuk sub-route:
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

    // first segment sudah cukup untuk semua: proyek, klien, karyawan, tahapan-aktif, tahapan-approval, dll
    return '/' . $first;
}

/** Seed menu_urls dari menus (kalau kosong), plus normalisasi & alias */
function _seed_menu_urls_from_menus(): void
{
    if (!isset($_SESSION['user'])) return;
    if (!empty($_SESSION['user']['menu_urls'])) return;

    $menus = $_SESSION['user']['menus'] ?? [];
    if (!$menus) return;

    $norm = fn(string $u) => canon_url($u);
    $urls = array_values(array_unique(array_filter(array_map(
        fn($m) => isset($m['url']) ? $norm($m['url']) : null,
        $menus
    ))));

    if (!in_array('/dashboard', $urls, true)) $urls[] = '/dashboard';

    // alias untuk menjaga kompatibilitas menu lama
    if (in_array('/tahapan_aktif', $urls, true) && !in_array('/tahapan-aktif', $urls, true)) {
        $urls[] = '/tahapan-aktif';
    }

    $_SESSION['user']['menu_urls'] = $urls;
}

/** Tangani AKSI (POST/GET tanpa layout) */
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

        if ($path === '/karyawan/store'  && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->store()  || true;
        if ($path === '/karyawan/update' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->update() || true;
        if ($path === '/karyawan/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->delete() || true;
        if ($path === '/karyawan/export' && $_SERVER['REQUEST_METHOD'] === 'GET')  return (bool)$c->exportCSV() || true;

        return false;
    }

    // === Klien
    if (is_route($path, '/klien')) {
        require_menu_access('/klien', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/KlienController.php';
        $c = new KlienController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/klien/store'  && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->store()  || true;
        if ($path === '/klien/update' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->update() || true;
        if ($path === '/klien/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->delete() || true;
        if ($path === '/klien/export' && $_SERVER['REQUEST_METHOD'] === 'GET')  return (bool)$c->exportCSV() || true;

        return false;
    }

    // === Tahapan (MASTER)
    if (is_route($path, '/tahapan')) {
        require_menu_access('/tahapan', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/TahapanController.php';
        $c = new TahapanController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/tahapan/store'  && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->store()  || true;
        if ($path === '/tahapan/update' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->update() || true;
        if ($path === '/tahapan/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->delete() || true;
        if ($path === '/tahapan/export' && $_SERVER['REQUEST_METHOD'] === 'GET')  return (bool)$c->exportCSV() || true;

        return false;
    }

    // === Proyek
    if (is_route($path, '/proyek')) {
        require_menu_access('/proyek', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/ProyekController.php';
        $c = new ProyekController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/proyek/store'  && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->store()  || true;
        if ($path === '/proyek/update' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->update() || true;
        if ($path === '/proyek/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->delete() || true;
        if ($path === '/proyek/export' && $_SERVER['REQUEST_METHOD'] === 'GET')  return (bool)$c->exportCSV() || true;

        // endpoint opsional
        if ($path === '/proyek/set-status' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->setStatus() || true;

        return false;
    }

    // === Pembayaran
    if (is_route($path, '/pembayaran')) {
        require_menu_access('/pembayaran', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/PembayaranController.php';
        $c = new PembayaranController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/pembayaran/store'  && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->store()  || true;
        if ($path === '/pembayaran/update' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->update() || true;
        if ($path === '/pembayaran/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->delete() || true;
        if ($path === '/pembayaran/export' && $_SERVER['REQUEST_METHOD'] === 'GET')  return (bool)$c->exportCSV() || true;

        return false;
    }

    // === Penjadwalan
    if (is_route($path, '/penjadwalan')) {
        require_menu_access('/penjadwalan', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/PenjadwalanController.php';
        $c = new PenjadwalanController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/penjadwalan/store'  && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->store()  || true;
        if ($path === '/penjadwalan/update' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->update() || true;
        if ($path === '/penjadwalan/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->delete() || true;

        return false;
    }

    // === Tahapan AKTIF (MANDOR)
    if (is_route($path, '/tahapan-aktif')) {
        require_menu_access('/tahapan-aktif', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/TahapanAktifController.php';
        $c = new TahapanAktifController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/tahapan-aktif/update' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->update() || true;

        return false;
    }

    // === Persetujuan Tahapan (PM/Admin)
    if (is_route($path, '/tahapan-approval')) {
        require_menu_access('/tahapan-approval', $BASE_URL, true);
        if (!empty($GLOBALS['__FORBIDDEN__'])) return false;

        require_once __DIR__ . '/../controllers/TahapanApprovalController.php';
        $c = new TahapanApprovalController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/tahapan-approval/approve' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->approve() || true;
        if ($path === '/tahapan-approval/reject'  && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->reject()  || true;

        return false;
    }

    // === Notifications (open/readall)
    if (is_route($path, '/notify')) {
        require_once __DIR__ . '/../controllers/NotificationController.php';
        $c = new NotificationController($GLOBALS['pdo'], $BASE_URL);

        if ($path === '/notify/readall' && $_SERVER['REQUEST_METHOD'] === 'POST') return (bool)$c->readAll() || true;
        if ($path === '/notify/open'    && $_SERVER['REQUEST_METHOD'] === 'GET')  return (bool)$c->open()    || true;

        return false;
    }

    return false;
}

/** Dispatch VIEW (GET) */
function dispatch_route(string $BASE_URL): void
{
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
        else echo '<div class="container mt-5"><div class="alert alert-danger">403 — Akses ditolak.</div></div>';
        return;
    }

    // ====== Standard Controller Routes ======

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
        if ($path === '/penjadwalan'                 && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->index();
            return;
        }
        if ($path === '/penjadwalan/edit'            && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $c->editForm();
            return;
        }
        if ($path === '/penjadwalan/detailproyek'    && $_SERVER['REQUEST_METHOD'] === 'GET') {
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

    // Persetujuan Tahapan
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
    $roleId = (string)($u['role_id'] ?? ($u['role_id_role'] ?? ($u['role'] ?? ($u['id_role'] ?? ''))));
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
        else echo '<div class="container mt-5"><div class="alert alert-warning">404 — Halaman tidak ditemukan.</div></div>';
        return;
    }

    include $view;
}
