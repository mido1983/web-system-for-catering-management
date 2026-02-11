<?php
namespace App\Models;

use App\Core\DB;

class IngredientModel
{
    public static function listAll(): array
    {
        $sql = 'SELECT id, name_he, unit FROM ingredients WHERE is_active = 1 ORDER BY name_he';
        $stmt = DB::conn()->query($sql);
        return $stmt->fetchAll();
    }
}