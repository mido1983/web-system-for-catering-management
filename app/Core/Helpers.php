<?php
use App\Services\AuthService;

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?array
    {
        return AuthService::currentUser();
    }
}

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($base === '/' || $base === '\\') {
            return '';
        }
        return rtrim($base, '/');
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = '/'): string
    {
        if ($path === '') {
            $path = '/';
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        return app_base_path() . $path;
    }
}
