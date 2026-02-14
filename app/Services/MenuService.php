<?php
namespace App\Services;

use App\Core\DB;
use App\Models\MenuModel;
use App\Models\MenuVersionModel;
use App\Models\MenuItemModel;
use App\Models\DishModel;

class MenuService
{
    public static function listMenusByAdmin(int $adminId): array
    {
        return MenuModel::listByAdmin($adminId);
    }

    public static function createMenuWithDraft(int $stationId, string $start, string $end): int
    {
        $existing = MenuModel::findByStationAndPeriod($stationId, $start, $end);
        if ($existing) {
            return (int)$existing['id'];
        }
        $menuId = MenuModel::create($stationId, $start, $end);
        MenuVersionModel::createDraft($menuId, 1);
        return $menuId;
    }

    public static function getMenuWithDraft(int $menuId, ?int $adminId = null): array
    {
        $menu = MenuModel::findById($menuId);
        if (!$menu) {
            throw new \RuntimeException('Menu not found');
        }
        $draft = MenuVersionModel::getDraftByMenu($menuId);
        if (!$draft) {
            $versions = MenuVersionModel::listByMenu($menuId);
            $next = empty($versions) ? 1 : ((int)$versions[0]['version_number'] + 1);
            $draftId = MenuVersionModel::createDraft($menuId, $next);
            $draft = ['id' => $draftId, 'menu_id' => $menuId, 'version_number' => $next, 'status' => 'DRAFT'];
        }
        $items = MenuItemModel::listByMenuVersion((int)$draft['id']);
        $dishes = DishModel::listAvailableForAdmin($adminId);
        return compact('menu', 'draft', 'items', 'dishes');
    }

    public static function saveDraftItems(int $menuVersionId, array $items): void
    {
        MenuItemModel::replaceItems($menuVersionId, $items);
    }

    public static function publish(int $menuId, int $userId): void
    {
        $draft = MenuVersionModel::getDraftByMenu($menuId);
        if (!$draft) {
            throw new \RuntimeException('No draft to publish');
        }

        DB::conn()->beginTransaction();
        try {
            MenuVersionModel::archivePublished($menuId);
            MenuVersionModel::publish((int)$draft['id'], $userId);
            DB::conn()->commit();
        } catch (\Throwable $e) {
            DB::conn()->rollBack();
            throw $e;
        }
    }

    public static function getPublishedMenuForDate(int $stationId, string $date): ?array
    {
        $menu = MenuModel::findMenuCoveringDate($stationId, $date);
        if (!$menu) {
            return null;
        }
        $published = MenuVersionModel::getPublishedByMenu((int)$menu['id']);
        if (!$published) {
            return null;
        }
        $items = MenuItemModel::listByMenuVersion((int)$published['id']);
        return [
            'menu' => $menu,
            'version' => $published,
            'items' => $items,
        ];
    }

    public static function latestPublishedVersionId(int $stationId, string $date): int
    {
        $menu = MenuModel::findMenuCoveringDate($stationId, $date);
        if (!$menu) {
            return 0;
        }
        $published = MenuVersionModel::getPublishedByMenu((int)$menu['id']);
        return $published ? (int)$published['id'] : 0;
    }
}