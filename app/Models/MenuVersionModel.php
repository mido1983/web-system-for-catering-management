<?php
namespace App\Models;

use App\Core\DB;

class MenuVersionModel
{
    public static function getDraftByMenu(int $menuId): ?array
    {
        $sql = "SELECT id, menu_id, version_number, status FROM menu_versions WHERE menu_id = :menu_id AND status = 'DRAFT' LIMIT 1";
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['menu_id' => $menuId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getPublishedByMenu(int $menuId): ?array
    {
        $sql = "SELECT id, menu_id, version_number, status, published_at FROM menu_versions WHERE menu_id = :menu_id AND status = 'PUBLISHED' LIMIT 1";
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['menu_id' => $menuId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function listByMenu(int $menuId): array
    {
        $sql = 'SELECT id, version_number, status, created_at, published_at FROM menu_versions WHERE menu_id = :menu_id ORDER BY version_number DESC';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['menu_id' => $menuId]);
        return $stmt->fetchAll();
    }

    public static function createDraft(int $menuId, int $versionNumber): int
    {
        $sql = "INSERT INTO menu_versions (menu_id, version_number, status, created_at) VALUES (:menu_id, :version_number, 'DRAFT', NOW())";
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['menu_id' => $menuId, 'version_number' => $versionNumber]);
        return (int)DB::conn()->lastInsertId();
    }

    public static function archivePublished(int $menuId): void
    {
        $sql = "UPDATE menu_versions SET status = 'ARCHIVED' WHERE menu_id = :menu_id AND status = 'PUBLISHED'";
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['menu_id' => $menuId]);
    }

    public static function publish(int $versionId, int $userId): void
    {
        $sql = "UPDATE menu_versions SET status = 'PUBLISHED', published_at = NOW(), published_by_user_id = :user_id WHERE id = :id";
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'id' => $versionId]);
    }
}