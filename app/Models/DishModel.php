<?php
namespace App\Models;

use App\Core\DB;

class DishModel
{
    public static function listAvailableForAdmin(?int $adminId): array
    {
        $sql = "SELECT id, name_he, category, owner_scope, owner_admin_id FROM dishes WHERE is_active = 1 AND (owner_scope = 'GLOBAL' OR (owner_scope = 'ADMIN' AND owner_admin_id = :admin_id)) ORDER BY name_he";
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['admin_id' => $adminId]);
        return $stmt->fetchAll();
    }

    public static function listByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT id, name_he, category FROM dishes WHERE id IN ($placeholders)";
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }
}