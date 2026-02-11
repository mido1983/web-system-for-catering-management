<?php
namespace App\Middleware;

class Csrf
{
    public static function validate(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (empty($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $token)) {
            http_response_code(400);
            echo 'בקשה לא תקינה.';
            exit;
        }
    }
}