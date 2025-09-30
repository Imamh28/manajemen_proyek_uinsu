<?php
// app/helpers/Audit.php
function audit_log(string $action, array $payload = []): void
{
    try {
        $user = $_SESSION['user'] ?? [];
        $row = [
            'ts'        => date('c'),
            'user_id'   => $user['id'] ?? null,
            'user_email' => $user['email'] ?? null,
            'ip'        => $_SERVER['REMOTE_ADDR'] ?? null,
            'action'    => $action,
            'payload'   => $payload,
        ];
        $dir = __DIR__ . '/../logs';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        $file = $dir . '/audit-' . date('Y-m-d') . '.log';
        @file_put_contents($file, json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    } catch (\Throwable $e) {
        // silent fail
    }
}
