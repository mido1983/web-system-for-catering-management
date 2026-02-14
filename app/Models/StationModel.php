<?php
namespace App\Models;

use App\Core\DB;

class StationModel
{
    public static function listByAdmin(int $adminId): array
    {
        $sql = 'SELECT id, name, admin_id, is_active, created_at FROM stations WHERE admin_id = :admin_id ORDER BY id DESC';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['admin_id' => $adminId]);
        return $stmt->fetchAll();
    }

    public static function listAll(): array
    {
        $sql = 'SELECT s.id, s.name, s.admin_id, s.is_active, s.created_at, u.email AS admin_email
                FROM stations s
                LEFT JOIN users u ON u.id = s.admin_id
                ORDER BY s.id DESC';
        $stmt = DB::conn()->query($sql);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $sql = 'SELECT id, name, admin_id, is_active FROM stations WHERE id = :id LIMIT 1';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $sql = 'INSERT INTO stations (name, admin_id, is_active, created_at) VALUES (:name, :admin_id, :is_active, NOW())';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute($data);
        return (int)DB::conn()->lastInsertId();
    }

    public static function updateName(int $id, string $name): void
    {
        $sql = 'UPDATE stations SET name = :name WHERE id = :id';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['name' => $name, 'id' => $id]);
    }

    public static function update(int $id, string $name, int $adminId, int $active): void
    {
        $sql = 'UPDATE stations SET name = :name, admin_id = :admin_id, is_active = :active WHERE id = :id';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'admin_id' => $adminId,
            'active' => $active,
        ]);
    }

    public static function setActive(int $id, int $active): void
    {
        $sql = 'UPDATE stations SET is_active = :active WHERE id = :id';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['active' => $active, 'id' => $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = DB::conn()->prepare('DELETE FROM stations WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
