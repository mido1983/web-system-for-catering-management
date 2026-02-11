<?php
namespace App;

use App\Middleware\Auth;
use App\Middleware\Csrf;

class Router
{
    private array $routes = [];

    public function get(string $pattern, callable $handler, array $options = []): void
    {
        $this->add('GET', $pattern, $handler, $options);
    }

    public function post(string $pattern, callable $handler, array $options = []): void
    {
        $this->add('POST', $pattern, $handler, $options);
    }

    private function add(string $method, string $pattern, callable $handler, array $options): void
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'options' => $options,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->match($route['pattern'], $path);
            if ($params === null) {
                continue;
            }

            $options = $route['options'] ?? [];
            if (!empty($options['auth'])) {
                Auth::requireLogin();
            }
            if (!empty($options['roles'])) {
                Auth::requireRole($options['roles']);
            }
            if ($method === 'POST' && empty($options['skip_csrf'])) {
                Csrf::validate();
            }

            call_user_func_array($route['handler'], $params);
            return;
        }

        http_response_code(404);
        echo 'הדף לא נמצא.';
    }

    private function match(string $pattern, string $path): ?array
    {
        $regex = preg_replace('#\{([^/]+)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }
        return $params;
    }
}