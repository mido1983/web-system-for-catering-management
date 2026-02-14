<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Services\MenuService;
use App\Services\ReportService;
use App\Services\SettingsService;
use App\Services\AuditService;
use App\Models\WasteReasonModel;
use App\Models\DailyWasteItemModel;

class StationController extends BaseController
{
    public function today(): void
    {
        $user = AuthService::currentUser();
        if (!$user) {
            $this->redirect('/login');
        }

        $stationId = (int)$user['station_id'];
        $today = date('Y-m-d');

        if (($_GET['check'] ?? '') === '1') {
            $latest = MenuService::latestPublishedVersionId($stationId, $today);
            header('Content-Type: application/json');
            echo json_encode(['version_id' => $latest]);
            return;
        }

        $menu = MenuService::getPublishedMenuForDate($stationId, $today);
        $existingReport = ReportService::getReportForDate($stationId, $today);
        $settings = SettingsService::getAll();

        $wasteItems = $existingReport ? DailyWasteItemModel::listByReport((int)$existingReport['id']) : [];
        $this->render('station/today', [
            'title' => 'היום',
            'menu' => $menu,
            'report' => $existingReport,
            'settings' => $settings,
            'waste_reasons' => WasteReasonModel::listActive(),
            'waste_items' => $wasteItems,
        ]);
    }

    public function submitToday(): void
    {
        $user = AuthService::currentUser();
        if (!$user) {
            $this->redirect('/login');
        }

        $stationId = (int)$user['station_id'];
        $today = date('Y-m-d');

        $menuVersionId = (int)($_POST['menu_version_id'] ?? 0);
        $menu = MenuService::getPublishedMenuForDate($stationId, $today);
        if (!$menu || (int)$menu['version']['id'] !== $menuVersionId) {
            $this->render('station/today', [
                'title' => 'היום',
                'menu' => $menu,
                'report' => ReportService::getReportForDate($stationId, $today),
                'settings' => SettingsService::getAll(),
                'waste_reasons' => WasteReasonModel::listActive(),
                'error' => 'התפריט לא תקין או עודכן. נסו לרענן.',
            ]);
            return;
        }
        $headcount = [
            'sibus_ok' => (int)($_POST['sibus_ok'] ?? 0),
            'sibus_manual' => (int)($_POST['sibus_manual'] ?? 0),
            'detainees' => (int)($_POST['detainees'] ?? 0),
        ];

        $waste = $_POST['waste'] ?? [];
        $comment = trim($_POST['comment'] ?? '');

        try {
            ReportService::saveDailyReport($stationId, $today, $menuVersionId, $headcount, $waste, $comment, (int)$user['id'], $user['role']);
            AuditService::log((int)$user['id'], 'daily_report_submit', 'daily_report', $today, null, ['station_id' => $stationId]);
            $this->redirect('/station/today?success=1');
        } catch (\Throwable $e) {
            $existingReport = ReportService::getReportForDate($stationId, $today);
            $this->render('station/today', [
                'title' => 'היום',
                'menu' => MenuService::getPublishedMenuForDate($stationId, $today),
                'report' => $existingReport,
                'settings' => SettingsService::getAll(),
                'waste_reasons' => WasteReasonModel::listActive(),
                'waste_items' => $existingReport ? DailyWasteItemModel::listByReport((int)$existingReport['id']) : [],
                'error' => 'שמירת הדוח נכשלה',
            ]);
        }
    }

    public function history(): void
    {
        $user = AuthService::currentUser();
        if (!$user) {
            $this->redirect('/login');
        }
        $stationId = (int)$user['station_id'];
        $reports = ReportService::getHistory($stationId, 30);

        $this->render('station/history', [
            'title' => 'היסטוריה',
            'reports' => $reports,
        ]);
    }
}
