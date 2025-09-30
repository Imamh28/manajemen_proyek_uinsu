<?php
// Simple session-based CSRF helper

function csrf_ensure_token(): void
{
    if (!isset($_SESSION)) return; // session dibuat di init.php
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
}

function csrf_token(): string
{
    csrf_ensure_token();
    return $_SESSION['_csrf_token'];
}

function csrf_verify(?string $token): bool
{
    csrf_ensure_token();
    return is_string($token) && hash_equals($_SESSION['_csrf_token'], $token);
}

function csrf_input(): string
{
    return '<input type="hidden" name="_csrf" value="' .
        htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}
