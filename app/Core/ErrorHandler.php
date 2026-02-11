<?php
namespace App\Core;

class ErrorHandler
{
    public static function register(): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ALL);

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        Logger::error('PHP error', ['message' => $message, 'file' => $file, 'line' => $line]);
        self::renderGenericError();
        return true;
    }

    public static function handleException(\Throwable $e): void
    {
        Logger::error('Uncaught exception', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        self::renderGenericError();
    }

    private static function renderGenericError(): void
    {
        http_response_code(500);
        echo 'שגיאת מערכת. נסו מאוחר יותר.';
        exit;
    }
}