<?php
namespace App\Services;

use App\Models\UserModel;
use App\Models\StationModel;

class AuthService
{
    private static ?array $currentUser = null;

    public static function attempt(string $email, string $password): ?array
    {
        $user = UserModel::findByEmail($email);
        if (!$user || (int)$user['is_active'] !== 1) {
            return null;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        UserModel::updateLastLogin((int)$user['id']);
        self::$currentUser = UserModel::findById((int)$user['id']);
        return self::$currentUser;
    }

    public static function currentUser(): ?array
    {
        if (self::$currentUser !== null) {
            return self::$currentUser;
        }
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }
        $user = UserModel::findById((int)$userId);
        if (!$user || (int)$user['is_active'] !== 1) {
            self::logout();
            return null;
        }
        self::$currentUser = $user;
        return $user;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function updatePassword(int $userId, string $password): void
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        UserModel::updatePassword($userId, $hash);
    }

    public static function homePath(): string
    {
        $user = self::currentUser();
        if (!$user) {
            return '/login';
        }
        if ($user['role'] === 'SUPERADMIN') {
            return '/sa/admins';
        }
        if ($user['role'] === 'ADMIN') {
            return '/admin/dashboard';
        }
        return '/station/today';
    }

    public static function adminOwnsStation(int $adminId, int $stationId): bool
    {
        $station = StationModel::findById($stationId);
        if (!$station) {
            return false;
        }
        return (int)$station['admin_id'] === $adminId;
    }
}