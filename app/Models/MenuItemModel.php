<?php
namespace App\Models;

use App\Core\DB;

class MenuItemModel
{
    public static function listByMenuVersion(int $menuVersionId): array
    {
        $sql = 'SELECT mi.dish_id, mi.planned_portions, d.name_he, d.category
                FROM menu_items mi
                INNER JOIN dishes d ON d.id = mi.dish_id
                WHERE mi.menu_version_id = :menu_version_id
                ORDER BY d.category, d.name_he';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['menu_version_id' => $menuVersionId]);
        return $stmt->fetchAll();
    }

    public static function replaceItems(int $menuVersionId, array $items): void
    {
        $delete = DB::conn()->prepare('DELETE FROM menu_items WHERE menu_version_id = :menu_version_id');
        $delete->execute(['menu_version_id' => $menuVersionId]);

        if (empty($items)) {
            return;
        }

        $sql = 'INSERT INTO menu_items (menu_version_id, dish_id, planned_portions) VALUES (:menu_version_id, :dish_id, :planned_portions)';
        $stmt = DB::conn()->prepare($sql);
        foreach ($items as $item) {
            $stmt->execute([
                'menu_version_id' => $menuVersionId,
                'dish_id' => $item['dish_id'],
                'planned_portions' => $item['planned_portions'],
            ]);
        }
    }
}