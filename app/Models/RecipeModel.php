<?php
namespace App\Models;

use App\Core\DB;

class RecipeModel
{
    public static function listAll(): array
    {
        $sql = 'SELECT id, dish_id, is_active, updated_at FROM recipes WHERE is_active = 1';
        $stmt = DB::conn()->query($sql);
        return $stmt->fetchAll();
    }
}