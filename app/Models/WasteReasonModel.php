<?php
namespace App\Models;

use App\Core\DB;

class WasteReasonModel
{
    public static function listActive(): array
    {
        $sql = 'SELECT id, name_he FROM waste_reasons WHERE is_active = 1 ORDER BY name_he';
        $stmt = DB::conn()->query($sql);
        return $stmt->fetchAll();
    }
}