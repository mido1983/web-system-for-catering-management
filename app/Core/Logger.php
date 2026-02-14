<?php
namespace App\Core;

class Logger
{
    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    private static function write(string $level, string $message, array $context = []): void
    {
        $config = require __DIR__ . '/../config.php';
        $file = $config['logging']['file'];
        $time = date('Y-m-d H:i:s');
        $line = sprintf("[%s] %s %s %s\n", $time, $level, $message, json_encode($context, JSON_UNESCAPED_UNICODE));
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}