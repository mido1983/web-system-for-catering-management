<?php
namespace App\Services;

use App\Models\SettingModel;

class SettingsService
{
    private static ?array $cache = null;

    public static function getAll(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        self::$cache = SettingModel::getAll();
        return self::$cache;
    }

    public static function update(string $key, string $value, int $userId): void
    {
        SettingModel::update($key, $value, $userId);
        self::$cache = null;
    }
}