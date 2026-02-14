<?php
namespace App\Models;

use App\Core\DB;

class UserModel
{
    public const ROLE_SUPERADMIN = 'SUPERADMIN';
    public const ROLE_DISTRICT_MANAGER = 'DISTRICT_MANAGER';
    public const ROLE_AREA_MANAGER = 'AREA_MANAGER';
    public const ROLE_STATION_MANAGER = 'STATION_MANAGER';
    public const ROLE_ADMIN_LEGACY = 'ADMIN';
    public const ROLE_STATION_USER = 'STATION_USER';

    public static function hierarchyRoles(): array
    {
        return [
            self::ROLE_SUPERADMIN,
            self::ROLE_DISTRICT_MANAGER,
            self::ROLE_AREA_MANAGER,
            self::ROLE_STATION_MANAGER,
            self::ROLE_STATION_USER,
        ];
    }

    public static function userAssignableRoles(): array
    {
        return [
            self::ROLE_SUPERADMIN,
            self::ROLE_DISTRICT_MANAGER,
            self::ROLE_AREA_MANAGER,
            self::ROLE_STATION_MANAGER,
            self::ROLE_STATION_USER,
            self::ROLE_ADMIN_LEGACY,
        ];
    }

    public static function canLoginRole(string $role): bool
    {
        return $role !== self::ROLE_STATION_USER;
    }

    public static function normalizedManagerRole(string $role): string
    {
        return $role === self::ROLE_ADMIN_LEGACY ? self::ROLE_STATION_MANAGER : $role;
    }

    public static function parentRoleFor(string $role): ?string
    {
        $role = self::normalizedManagerRole($role);
        if ($role === self::ROLE_SUPERADMIN) {
            return null;
        }
        if ($role === self::ROLE_DISTRICT_MANAGER) {
            return self::ROLE_SUPERADMIN;
        }
        if ($role === self::ROLE_AREA_MANAGER) {
            return self::ROLE_DISTRICT_MANAGER;
        }
        if ($role === self::ROLE_STATION_MANAGER) {
            return self::ROLE_AREA_MANAGER;
        }
        if ($role === self::ROLE_STATION_USER) {
            return self::ROLE_STATION_MANAGER;
        }
        return null;
    }

    public static function jobTitles(): array
    {
        return [
            'שף ראשי קייטרינג',
            'שף תפעולי',
            'סו שף קייטרינג',
            'מנהל ייצור מטבח',
            'אחראי משמרת ייצור',
            'טבח ייצור חם',
            'טבח ייצור קר',
            'טבח הכנות',
            'אחראי פס אריזה',
            'קצב',
            'אחראי תנורים / קומבי',
            'אחראי הזמנות וספקים',
            'אחראי מלאי ומחסן',
            'אחראי ציוד ואירועים',
            'אחראי העמסה ושילוח',
            'אחראי בטיחות מזון',
            'אחראי ניקיון ותברואה',
            'אחראי בקרת איכות מנות',
            'עוזר טבח',
            'אורזים',
            'שוטפי כלים',
        ];
    }

    public static function findByEmail(string $email): ?array
    {
        $sql = 'SELECT id, email, password_hash, role, admin_id, station_id, job_title, first_name, last_name, phone, work_hours, must_change_password, is_active, last_login_at FROM users WHERE email = :email LIMIT 1';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $sql = 'SELECT id, email, role, admin_id, station_id, job_title, first_name, last_name, phone, work_hours, must_change_password, is_active, last_login_at FROM users WHERE id = :id LIMIT 1';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updateLastLogin(int $id): void
    {
        $sql = 'UPDATE users SET last_login_at = NOW() WHERE id = :id';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    public static function updatePassword(int $id, string $hash): void
    {
        $sql = 'UPDATE users SET password_hash = :hash, must_change_password = 0 WHERE id = :id';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['hash' => $hash, 'id' => $id]);
    }

    public static function setMustChangePassword(int $id, string $hash): void
    {
        $sql = 'UPDATE users SET password_hash = :hash, must_change_password = 1 WHERE id = :id';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['hash' => $hash, 'id' => $id]);
    }

    public static function updateEmail(int $id, string $email): void
    {
        $sql = 'UPDATE users SET email = :email WHERE id = :id';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['email' => $email, 'id' => $id]);
    }

    public static function setActive(int $id, int $active): void
    {
        $sql = 'UPDATE users SET is_active = :active WHERE id = :id';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['active' => $active, 'id' => $id]);
    }

    public static function create(array $data): int
    {
        $sql = 'INSERT INTO users (email, password_hash, role, admin_id, station_id, job_title, first_name, last_name, phone, work_hours, must_change_password, is_active, created_at)
                VALUES (:email, :password_hash, :role, :admin_id, :station_id, :job_title, :first_name, :last_name, :phone, :work_hours, :must_change_password, :is_active, NOW())';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute($data);
        return (int)DB::conn()->lastInsertId();
    }

    public static function listAdmins(): array
    {
        $sql = "SELECT id, email, first_name, last_name, phone, work_hours, is_active, created_at
                FROM users
                WHERE role IN ('ADMIN','STATION_MANAGER')
                ORDER BY id DESC";
        $stmt = DB::conn()->query($sql);
        return $stmt->fetchAll();
    }

    public static function listManagersByRole(string $role): array
    {
        $sql = 'SELECT id, email, first_name, last_name, role
                FROM users
                WHERE role = :role AND is_active = 1
                ORDER BY email ASC';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll();
    }

    public static function listStationUsersByAdmin(int $adminId): array
    {
        $sql = 'SELECT u.id, u.email, u.station_id, u.job_title, u.first_name, u.last_name, u.phone, u.work_hours, u.is_active, u.created_at, s.name AS station_name
                FROM users u
                LEFT JOIN stations s ON s.id = u.station_id
                WHERE u.role = "STATION_USER" AND u.admin_id = :admin_id
                ORDER BY u.id DESC';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['admin_id' => $adminId]);
        return $stmt->fetchAll();
    }

    public static function findStationUserByStationId(int $stationId): ?array
    {
        $sql = 'SELECT id, email FROM users WHERE role = \"STATION_USER\" AND station_id = :station_id LIMIT 1';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['station_id' => $stationId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function listAllUsers(): array
    {
        $sql = 'SELECT u.id, u.email, u.role, u.admin_id, u.station_id, u.is_active, u.created_at,
                       u.job_title, u.first_name, u.last_name, u.phone, u.work_hours, a.email AS admin_email, s.name AS station_name
                FROM users u
                LEFT JOIN users a ON a.id = u.admin_id
                LEFT JOIN stations s ON s.id = u.station_id
                ORDER BY u.id DESC';
        $stmt = DB::conn()->query($sql);
        return $stmt->fetchAll();
    }

    public static function updateUser(int $id, array $data): void
    {
        $sql = 'UPDATE users
                SET email = :email, role = :role, admin_id = :admin_id, station_id = :station_id, job_title = :job_title, first_name = :first_name, last_name = :last_name, phone = :phone, work_hours = :work_hours, is_active = :is_active
                WHERE id = :id';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'email' => $data['email'],
            'role' => $data['role'],
            'admin_id' => $data['admin_id'],
            'station_id' => $data['station_id'],
            'job_title' => $data['job_title'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'work_hours' => $data['work_hours'],
            'is_active' => $data['is_active'],
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = DB::conn()->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
