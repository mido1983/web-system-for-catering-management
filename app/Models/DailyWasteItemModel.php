<?php
namespace App\Models;

use App\Core\DB;

class DailyWasteItemModel
{
    public static function listByReport(int $reportId): array
    {
        $sql = 'SELECT dish_id, leftover_grams, thrown_grams, waste_reason_id, note
                FROM daily_waste_items WHERE daily_report_id = :id';
        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['id' => $reportId]);
        return $stmt->fetchAll();
    }

    public static function deleteByReport(int $reportId): void
    {
        $stmt = DB::conn()->prepare('DELETE FROM daily_waste_items WHERE daily_report_id = :id');
        $stmt->execute(['id' => $reportId]);
    }

    public static function insertMany(int $reportId, array $items): void
    {
        if (empty($items)) {
            return;
        }
        $sql = 'INSERT INTO daily_waste_items (daily_report_id, dish_id, leftover_grams, thrown_grams, waste_reason_id, note)
                VALUES (:daily_report_id, :dish_id, :leftover_grams, :thrown_grams, :waste_reason_id, :note)';
        $stmt = DB::conn()->prepare($sql);
        foreach ($items as $item) {
            $stmt->execute([
                'daily_report_id' => $reportId,
                'dish_id' => $item['dish_id'],
                'leftover_grams' => $item['leftover_grams'],
                'thrown_grams' => $item['thrown_grams'],
                'waste_reason_id' => $item['waste_reason_id'],
                'note' => $item['note'],
            ]);
        }
    }
}
