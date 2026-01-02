<?php
// app/middleware/authorize.php
// Harus dipanggil SETELAH init.php & auth.php.

function mark_forbidden(): void
{
    if (!headers_sent()) {
        http_response_code(403);
    }
    $GLOBALS['__FORBIDDEN__'] = true;
}

/**
 * Ambil role id dari session secara robust.
 * Mendukung variasi key yang sering terjadi: role_id, role_id_role, id_role, role, dll.
 */
function session_role_id(): string
{
    $u = $_SESSION['user'] ?? [];

    $candidates = [
        $u['role_id'] ?? null,
        $u['role_id_role'] ?? null,
        $u['id_role'] ?? null,
        $u['role'] ?? null,
        $u['roleId'] ?? null,
    ];

    foreach ($candidates as $c) {
        if ($c === null) continue;
        $v = is_string($c) ? trim($c) : (string)$c;
        if ($v !== '') return $v;
    }

    return '';
}

/**
 * Normalisasi route:
 * - bisa input: "proyek", "/proyek", "index.php?r=proyek/edit&id=PRJ001"
 * - output: "/proyek/edit"
 */
function canon_url(string $u): string
{
    $u = trim($u);
    if ($u === '') return '/';

    // 1) Jika bentuknya index.php?r=...
    // parse_url aman untuk mengambil query
    $parsed = @parse_url($u);

    // Ambil route dari query param r jika ada
    if (is_array($parsed) && !empty($parsed['query'])) {
        $q = [];
        parse_str($parsed['query'], $q);

        // Jika ada r=..., jadikan itu sebagai route utama
        if (!empty($q['r'])) {
            $route = (string)$q['r'];
            $route = trim($route);

            // route bisa "proyek/edit" => jadikan "/proyek/edit"
            if ($route !== '') {
                $u = $route;
            }
        }
    }

    // 2) Jika input masih mengandung ?r= tanpa parse_url (kasus string unik)
    if (str_contains($u, '?r=')) {
        $parts = explode('?r=', $u, 2);
        $after = $parts[1] ?? '';
        // berhenti sebelum &
        $after = explode('&', $after, 2)[0] ?? $after;
        $after = trim($after);
        if ($after !== '') $u = $after;
    }

    // 3) Normalisasi dasar
    $u = strtolower(trim($u));

    // buang querystring kalau masih ada
    $u = explode('?', $u, 2)[0];
    $u = explode('#', $u, 2)[0];

    if ($u === '') return '/';

    // pastikan mulai dengan '/'
    if ($u[0] !== '/') $u = '/' . $u;

    // alias khusus: tahapan_aktif -> tahapan-aktif
    if ($u === '/tahapan_aktif' || str_starts_with($u, '/tahapan_aktif/')) {
        $u = str_replace('/tahapan_aktif', '/tahapan-aktif', $u);
    }

    // rapikan trailing slash
    $u = rtrim($u, '/');
    return $u === '' ? '/' : $u;
}

/**
 * Cek akses menu berbasis URL dari session.
 * - $allowPrefix: true → "/proyek/*" sah jika "/proyek" diizinkan
 *
 * Catatan:
 * - Pastikan session menyimpan menu URL dalam bentuk yang konsisten,
 *   misalnya: ["proyek", "/proyek", "index.php?r=proyek"] -> akan dinormalisasi.
 */
function require_menu_access(string $url, string $baseUrl, bool $allowPrefix = true): bool
{
    if (!isset($_SESSION['user'])) {
        header('Location: ' . rtrim($baseUrl, '/') . '/auth/login.php');
        exit;
    }

    $target = canon_url($url);

    $allowedRaw = $_SESSION['user']['menu_urls'] ?? [];
    if (!is_array($allowedRaw)) $allowedRaw = [];

    $allowed = array_values(array_filter(array_map('canon_url', $allowedRaw)));

    // match exact
    $isAllowed = in_array($target, $allowed, true);

    // match prefix: "/proyek/edit" diizinkan jika "/proyek" ada
    if (!$isAllowed && $allowPrefix) {
        foreach ($allowed as $a) {
            if ($a === '' || $a === '/') continue;
            if ($target === $a) {
                $isAllowed = true;
                break;
            }
            if (str_starts_with($target, $a . '/')) {
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

/**
 * Batasi akses berdasarkan daftar role ID (misal: RL001, RL002).
 * Versi ini robust terhadap variasi key session role.
 */
function require_roles(array $allowedRoleIds, string $baseUrl): bool
{
    if (!isset($_SESSION['user'])) {
        header('Location: ' . rtrim($baseUrl, '/') . '/auth/login.php');
        exit;
    }

    $userRole = session_role_id();
    if ($userRole === '' || !in_array($userRole, $allowedRoleIds, true)) {
        mark_forbidden();
        return false;
    }

    return true;
}
