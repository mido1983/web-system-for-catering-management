<?php
namespace App\Middleware;

use App\Services\AuthService;

class Auth
{
    public static function requireLogin(): void
    {
        if (!AuthService::currentUser()) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole(array $roles): void
    {
        $user = AuthService::currentUser();
        if (!$user || !in_array($user['role'], $roles, true)) {
            http_response_code(403);
            echo 'אין הרשאה.';
            exit;
        }
    }

    public static function requireStationScope(int $stationId): void
    {
        $user = AuthService::currentUser();
        if (!$user) {
            http_response_code(403);
            echo 'אין הרשאה.';
            exit;
        }

        if ($user['role'] === 'SUPERADMIN') {
            return;
        }

        if ($user['role'] === 'ADMIN') {
            if (!AuthService::adminOwnsStation($user['id'], $stationId)) {
                http_response_code(403);
                echo 'אין הרשאה.';
                exit;
            }
            return;
        }

        if ($user['role'] === 'STATION_USER') {
            if ((int)$user['station_id'] !== $stationId) {
                http_response_code(403);
                echo 'אין הרשאה.';
                exit;
            }
            return;
        }
    }
}