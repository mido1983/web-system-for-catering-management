<?php
namespace App\Core;

class ErrorHandler
{
    private static bool $debug = false;

    public static function register(): void
    {
        $config = require __DIR__ . '/../config.php';
        self::$debug = (bool)($config['app']['debug'] ?? false);

        ini_set('display_errors', '0');
        error_reporting(E_ALL);

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        Logger::error('PHP error', ['message' => $message, 'file' => $file, 'line' => $line]);
        error_log("PHP error: {$message} in {$file}:{$line}");
        self::renderGenericError($message);
        return true;
    }

    public static function handleException(\Throwable $e): void
    {
        Logger::error('Uncaught exception', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        error_log('Uncaught exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        self::renderGenericError($e->getMessage());
    }

    private static function renderGenericError(?string $debugMessage = null): void
    {
        http_response_code(500);
        if (self::$debug && $debugMessage) {
            echo 'שגיאת מערכת: ' . htmlspecialchars($debugMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        } else {
            echo 'שגיאת מערכת. נסו מאוחר יותר.';
        }
        exit;
    }
}
