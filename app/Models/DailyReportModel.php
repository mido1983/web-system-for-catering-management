<?php
namespace App\Models;

use App\Core\DB;

class DailyReportModel
{
    public static function findByStationAndDate(int $stationId, string $date): ?array
    {
        $sql = 'SELECT id, station_id, date, menu_version_id, sibus_ok, sibus_manual, detainees, comment, submitted_at, submitted_by_user_id
                FROM daily_reports WHERE station_id = :station_id AND date = :date LIMIT 1';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['station_id' => $stationId, 'date' => $date]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $sql = 'SELECT id, station_id, date, menu_version_id, sibus_ok, sibus_manual, detainees, comment, submitted_at, submitted_by_user_id
                FROM daily_reports WHERE id = :id LIMIT 1';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function insert(array $data): int
    {
        $sql = 'INSERT INTO daily_reports (station_id, date, menu_version_id, sibus_ok, sibus_manual, detainees, comment, submitted_at, submitted_by_user_id)
                VALUES (:station_id, :date, :menu_version_id, :sibus_ok, :sibus_manual, :detainees, :comment, NOW(), :submitted_by_user_id)';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute($data);
        return (int)DB::conn()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $sql = 'UPDATE daily_reports SET sibus_ok = :sibus_ok, sibus_manual = :sibus_manual, detainees = :detainees, comment = :comment, submitted_at = NOW(), submitted_by_user_id = :submitted_by_user_id WHERE id = :id';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute($data + ['id' => $id]);
    }

    public static function listHistory(int $stationId, int $limit): array
    {
        $sql = 'SELECT date, sibus_ok, sibus_manual, detainees, submitted_at FROM daily_reports WHERE station_id = :station_id ORDER BY date DESC LIMIT :limit';
        $stmt = DB::conn()->prepare($sql);
        $stmt->bindValue(':station_id', $stationId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function listByAdmin(int $adminId, array $filters): array
    {
        $params = ['admin_id' => $adminId];
        $where = 's.admin_id = :admin_id';

        if (!empty($filters['station_id'])) {
            $where .= ' AND r.station_id = :station_id';
            $params['station_id'] = (int)$filters['station_id'];
        }
        if (!empty($filters['date_from'])) {
            $where .= ' AND r.date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where .= ' AND r.date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        $sql = 'SELECT r.id, r.station_id, r.date, r.sibus_ok, r.sibus_manual, r.detainees, r.submitted_at, s.name AS station_name
                FROM daily_reports r
                INNER JOIN stations s ON s.id = r.station_id
                WHERE ' . $where . '
                ORDER BY r.date DESC
                LIMIT 200';

        $stmt = DB::conn()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
