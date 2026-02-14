<?php
namespace App\Models;

use App\Core\DB;

class MenuModel
{
    public static function listByAdmin(int $adminId): array
    {
        $sql = 'SELECT m.id, m.station_id, m.period_start, m.period_end, s.name AS station_name
                FROM menus m
                INNER JOIN stations s ON s.id = m.station_id
                WHERE s.admin_id = :admin_id
                ORDER BY m.period_start DESC';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['admin_id' => $adminId]);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $sql = 'SELECT id, station_id, period_start, period_end FROM menus WHERE id = :id LIMIT 1';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByStationAndPeriod(int $stationId, string $start, string $end): ?array
    {
        $sql = 'SELECT id, station_id, period_start, period_end FROM menus WHERE station_id = :station_id AND period_start = :start AND period_end = :end LIMIT 1';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['station_id' => $stationId, 'start' => $start, 'end' => $end]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(int $stationId, string $start, string $end): int
    {
        $sql = 'INSERT INTO menus (station_id, period_start, period_end, created_at) VALUES (:station_id, :start, :end, NOW())';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['station_id' => $stationId, 'start' => $start, 'end' => $end]);
        return (int)DB::conn()->lastInsertId();
    }

    public static function findMenuCoveringDate(int $stationId, string $date): ?array
    {
        $sql = 'SELECT id, station_id, period_start, period_end FROM menus WHERE station_id = :station_id AND period_start <= :date AND period_end >= :date LIMIT 1';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['station_id' => $stationId, 'date' => $date]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}