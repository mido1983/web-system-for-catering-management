<?php
namespace App\Services;

use App\Core\DB;
use App\Models\DailyReportModel;
use App\Models\DailyWasteItemModel;

class ReportService
{
    public static function getReportForDate(int $stationId, string $date): ?array
    {
        return DailyReportModel::findByStationAndDate($stationId, $date);
    }

    public static function getHistory(int $stationId, int $limit): array
    {
        return DailyReportModel::listHistory($stationId, $limit);
    }

    public static function saveDailyReport(int $stationId, string $date, int $menuVersionId, array $headcount, array $waste, string $comment, int $userId, string $role): void
    {
        $settings = SettingsService::getAll();
        $deadline = $settings['deadline_time'] ?? '20:00';
        $now = new \DateTime('now');
        $deadlineAt = new \DateTime($date . ' ' . $deadline . ':00');

        if ($role === 'STATION_USER' && $now > $deadlineAt) {
            throw new \RuntimeException('Deadline passed');
        }

        if ($headcount['sibus_ok'] < 0 || $headcount['sibus_manual'] < 0 || $headcount['detainees'] < 0) {
            throw new \RuntimeException('Invalid headcount');
        }

        $report = DailyReportModel::findByStationAndDate($stationId, $date);

        $items = [];
        foreach ($waste as $dishId => $item) {
            $leftKg = (float)($item['leftover_kg'] ?? 0);
            $thrownKg = (float)($item['thrown_kg'] ?? 0);
            $leftover = (int)round($leftKg * 1000);
            $thrown = (int)round($thrownKg * 1000);
            $reasonId = isset($item['waste_reason_id']) && $item['waste_reason_id'] !== '' ? (int)$item['waste_reason_id'] : null;
            $note = trim($item['note'] ?? '');

            if ($thrown > 0 && !$reasonId) {
                throw new \RuntimeException('Waste reason required');
            }
            if ($leftover < 0 || $thrown < 0) {
                throw new \RuntimeException('Invalid waste');
            }

            $items[] = [
                'dish_id' => (int)$dishId,
                'leftover_grams' => $leftover,
                'thrown_grams' => $thrown,
                'waste_reason_id' => $reasonId,
                'note' => $note,
            ];
        }

        DB::conn()->beginTransaction();
        try {
            if ($report) {
                DailyReportModel::update((int)$report['id'], [
                    'sibus_ok' => $headcount['sibus_ok'],
                    'sibus_manual' => $headcount['sibus_manual'],
                    'detainees' => $headcount['detainees'],
                    'comment' => $comment !== '' ? $comment : null,
                    'submitted_by_user_id' => $userId,
                ]);
                DailyWasteItemModel::deleteByReport((int)$report['id']);
                DailyWasteItemModel::insertMany((int)$report['id'], $items);
            } else {
                $reportId = DailyReportModel::insert([
                    'station_id' => $stationId,
                    'date' => $date,
                    'menu_version_id' => $menuVersionId,
                    'sibus_ok' => $headcount['sibus_ok'],
                    'sibus_manual' => $headcount['sibus_manual'],
                    'detainees' => $headcount['detainees'],
                    'comment' => $comment !== '' ? $comment : null,
                    'submitted_by_user_id' => $userId,
                ]);
                DailyWasteItemModel::insertMany($reportId, $items);
            }
            DB::conn()->commit();
        } catch (\Throwable $e) {
            DB::conn()->rollBack();
            throw $e;
        }
    }

    public static function listReportsByAdmin(int $adminId, array $filters): array
    {
        return DailyReportModel::listByAdmin($adminId, $filters);
    }
}
