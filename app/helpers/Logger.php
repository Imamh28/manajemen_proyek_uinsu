<?php
class Logger
{
    public static function ensureDir($dir)
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    public static function log($file, $message)
    {
        $dir = __DIR__ . '/../logs';
        self::ensureDir($dir);
        $path = $dir . '/' . $file;
        @file_put_contents($path, date('c') . ' - ' . $message . PHP_EOL, FILE_APPEND);
    }
}
