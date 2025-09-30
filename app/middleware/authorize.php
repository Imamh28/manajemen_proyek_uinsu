<?php
// Harus dipanggil SETELAH init.php & auth.php.

function mark_forbidden(): void
{
    if (!headers_sent()) http_response_code(403);
    $GLOBALS['__FORBIDDEN__'] = true;
}

/** Normalisasi URL + alias */
function canon_url(string $u): string
{
    $u = strtolower(trim($u));
    if ($u === '') return '/';
    if ($u[0] !== '/') $u = '/' . $u;
    // alias khusus: tahapan_aktif -> tahapan-aktif
    if ($u === '/tahapan_aktif' || str_starts_with($u, '/tahapan_aktif/')) {
        $u = str_replace('/tahapan_aktif', '/tahapan-aktif', $u);
    }
    $u = rtrim($u, '/');
    return $u === '' ? '/' : $u;
}

/**
 * Cek akses menu berbasis URL dari session.
 * - $allowPrefix: true → "/proyek/*" sah jika "/proyek" diizinkan
 */
function require_menu_access(string $url, string $baseUrl, bool $allowPrefix = true): bool
{
    if (!isset($_SESSION['user'])) {
        header('Location: ' . $baseUrl . 'auth/login.php');
        exit;
    }

    $target = canon_url($url);

    $allowed = array_map('canon_url', $_SESSION['user']['menu_urls'] ?? []);
    $isAllowed = in_array($target, $allowed, true);

    if (!$isAllowed && $allowPrefix) {
        foreach ($allowed as $a) {
            if ($a !== '' && $a !== '/' && str_starts_with($target, $a . '/')) {
                $isAllowed = true;
                break;
            }
        }
    }

    if (!$isAllowed) {
        mark_forbidden();
        return false;
    }
    return true;
}

/** Batasi akses berdasarkan daftar role ID. */
function require_roles(array $allowedRoleIds, string $baseUrl): bool
{
    if (!isset($_SESSION['user'])) {
        header('Location: ' . $baseUrl . 'auth/login.php');
        exit;
    }
    $userRole = $_SESSION['user']['role_id'] ?? null;
    if (!$userRole || !in_array($userRole, $allowedRoleIds, true)) {
        mark_forbidden();
        return false;
    }
    return true;
}
