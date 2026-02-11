<?php
namespace App\Models;

use App\Core\DB;

class SettingModel
{
    public static function getAll(): array
    {
        $sql = 'SELECT key_name, value_text FROM settings';
        $stmt = DB::conn()->query($sql);
        $rows = $stmt->fetchAll();
        $out = [];
        foreach ($rows as $row) {
            $out[$row['key_name']] = $row['value_text'];
        }
        return $out;
    }

    public static function update(string $key, string $value, int $userId): void
    {
        $sql = 'UPDATE settings SET value_text = :value, updated_at = NOW(), updated_by_user_id = :user_id WHERE key_name = :key_name';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['value' => $value, 'user_id' => $userId, 'key_name' => $key]);
    }
}