<?php
namespace App\Models;

use App\Core\DB;

class StationModel
{
    public static function listByAdmin(int $adminId): array
    {
        $sql = 'SELECT id, name, admin_id, is_active, is_cooking_kitchen, created_at FROM stations WHERE admin_id = :admin_id ORDER BY id DESC';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['admin_id' => $adminId]);
        return $stmt->fetchAll();
    }

    public static function listAll(): array
    {
        $sql = 'SELECT s.id, s.name, s.admin_id, s.is_active, s.is_cooking_kitchen, s.created_at, u.email AS admin_email
                FROM stations s
                LEFT JOIN users u ON u.id = s.admin_id
                ORDER BY s.id DESC';
        $stmt = DB::conn()->query($sql);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $sql = 'SELECT id, name, admin_id, is_active, is_cooking_kitchen FROM stations WHERE id = :id LIMIT 1';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $sql = 'INSERT INTO stations (name, admin_id, is_active, is_cooking_kitchen, created_at)
                VALUES (:name, :admin_id, :is_active, :is_cooking_kitchen, NOW())';
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

    public static function update(int $id, string $name, int $adminId, int $active, int $isCookingKitchen = 0): void
    {
        $sql = 'UPDATE stations
                SET name = :name, admin_id = :admin_id, is_active = :active, is_cooking_kitchen = :is_cooking_kitchen
                WHERE id = :id';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'admin_id' => $adminId,
            'active' => $active,
            'is_cooking_kitchen' => $isCookingKitchen,
        ]);
    }

    public static function listSupplyTargets(int $sourceStationId): array
    {
        $sql = 'SELECT target_station_id FROM station_supply_links WHERE source_station_id = :source_station_id';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['source_station_id' => $sourceStationId]);
        return array_map(static fn(array $r): int => (int)$r['target_station_id'], $stmt->fetchAll());
    }

    public static function replaceSupplyTargets(int $sourceStationId, array $targetStationIds): void
    {
        $conn = DB::conn();
        $conn->beginTransaction();
        try {
            $deleteStmt = $conn->prepare('DELETE FROM station_supply_links WHERE source_station_id = :source_station_id');
            $deleteStmt->execute(['source_station_id' => $sourceStationId]);

            if (!empty($targetStationIds)) {
                $insertStmt = $conn->prepare(
                    'INSERT INTO station_supply_links (source_station_id, target_station_id, created_at)
                     VALUES (:source_station_id, :target_station_id, NOW())'
                );
                foreach ($targetStationIds as $targetStationId) {
                    $targetStationId = (int)$targetStationId;
                    if ($targetStationId < 1 || $targetStationId === $sourceStationId) {
                        continue;
                    }
                    $insertStmt->execute([
                        'source_station_id' => $sourceStationId,
                        'target_station_id' => $targetStationId,
                    ]);
                }
            }

            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
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
