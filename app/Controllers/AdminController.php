<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Services\MenuService;
use App\Services\ReportService;
use App\Services\PlannerService;
use App\Services\SettingsService;
use App\Services\AuditService;
use App\Models\StationModel;
use App\Models\UserModel;
use App\Models\MenuVersionModel;
use App\Models\MenuItemModel;
use App\Models\WasteReasonModel;
use App\Models\AuditLogModel;
use App\Models\DailyReportModel;
use App\Models\DailyWasteItemModel;

class AdminController extends BaseController
{
    public function dashboard(): void
    {
        $user = AuthService::currentUser();
        $stations = StationModel::listByAdmin((int)$user['id']);
        $this->render('admin/dashboard', [
            'title' => 'דשבורד',
            'stations' => $stations,
        ]);
    }

    public function stations(): void
    {
        $user = AuthService::currentUser();
        $stations = StationModel::listByAdmin((int)$user['id']);

        $this->render('admin/stations', [
            'title' => 'תחנות',
            'stations' => $stations,
        ]);
    }

    public function updateStation(): void
    {
        $user = AuthService::currentUser();
        $stationId = (int)($_POST['station_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($stationId < 1 || $name === '') {
            $this->redirect('/admin/stations');
        }

        $station = StationModel::findById($stationId);
        if (!$station || (int)$station['admin_id'] !== (int)$user['id']) {
            http_response_code(403);
            echo 'אין הרשאה.';
            return;
        }

        $before = $station;
        StationModel::updateName($stationId, $name);
        $after = StationModel::findById($stationId);
        AuditService::log((int)$user['id'], 'station_update', 'station', (string)$stationId, $before, $after);

        $this->redirect('/admin/stations');
    }

    public function users(): void
    {
        $user = AuthService::currentUser();
        $stationUsers = UserModel::listStationUsersByAdmin((int)$user['id']);
        $stations = StationModel::listByAdmin((int)$user['id']);
        $this->render('admin/users', [
            'title' => 'משתמשי תחנה',
            'users' => $stationUsers,
            'stations' => $stations,
        ]);
    }

    public function createStationUser(): void
    {
        $user = AuthService::currentUser();
        $email = trim($_POST['email'] ?? '');
        $stationId = (int)($_POST['station_id'] ?? 0);
        $tempPassword = trim($_POST['temp_password'] ?? '');

        if ($email === '' || $stationId < 1 || $tempPassword === '') {
            $this->redirect('/admin/users');
        }

        $station = StationModel::findById($stationId);
        if (!$station || (int)$station['admin_id'] !== (int)$user['id']) {
            http_response_code(403);
            echo 'אין הרשאה.';
            return;
        }

        if (UserModel::findStationUserByStationId($stationId)) {
            $this->redirect('/admin/users?error=station_has_user');
        }

        $hash = password_hash($tempPassword, PASSWORD_BCRYPT);
        $newId = UserModel::create([
            'email' => $email,
            'password_hash' => $hash,
            'role' => 'STATION_USER',
            'admin_id' => $user['id'],
            'station_id' => $stationId,
            'must_change_password' => 1,
            'is_active' => 1,
        ]);

        AuditService::log((int)$user['id'], 'user_create', 'user', (string)$newId, null, ['email' => $email, 'role' => 'STATION_USER']);
        $this->redirect('/admin/users');
    }

    public function resetUserPassword(): void
    {
        $user = AuthService::currentUser();
        $userId = (int)($_POST['user_id'] ?? 0);
        $tempPassword = trim($_POST['temp_password'] ?? '');
        if ($userId < 1 || $tempPassword === '') {
            $this->redirect('/admin/users');
        }

        $stationUsers = UserModel::listStationUsersByAdmin((int)$user['id']);
        $allowed = array_filter($stationUsers, fn($u) => (int)$u['id'] === $userId);
        if (!$allowed) {
            http_response_code(403);
            echo 'אין הרשאה.';
            return;
        }

        $hash = password_hash($tempPassword, PASSWORD_BCRYPT);
        UserModel::setMustChangePassword($userId, $hash);
        AuditService::log((int)$user['id'], 'password_reset', 'user', (string)$userId, null, null);
        $this->redirect('/admin/users');
    }

    public function toggleUser(): void
    {
        $user = AuthService::currentUser();
        $userId = (int)($_POST['user_id'] ?? 0);
        $active = (int)($_POST['is_active'] ?? 0);
        $stationUsers = UserModel::listStationUsersByAdmin((int)$user['id']);
        $allowed = array_filter($stationUsers, fn($u) => (int)$u['id'] === $userId);
        if (!$allowed) {
            http_response_code(403);
            echo 'אין הרשאה.';
            return;
        }
        UserModel::setActive($userId, $active);
        AuditService::log((int)$user['id'], 'user_toggle', 'user', (string)$userId, null, ['is_active' => $active]);
        $this->redirect('/admin/users');
    }

    public function menus(): void
    {
        $user = AuthService::currentUser();
        $menus = MenuService::listMenusByAdmin((int)$user['id']);
        $stations = StationModel::listByAdmin((int)$user['id']);
        $this->render('admin/menus', [
            'title' => 'תפריטים',
            'menus' => $menus,
            'stations' => $stations,
        ]);
    }

    public function createMenu(): void
    {
        $user = AuthService::currentUser();
        $stationId = (int)($_POST['station_id'] ?? 0);
        $start = $_POST['period_start'] ?? '';
        $end = $_POST['period_end'] ?? '';

        if ($stationId < 1 || !$start || !$end) {
            $this->redirect('/admin/menus');
        }
        if (strtotime($start) === false || strtotime($end) === false || $start > $end) {
            $this->redirect('/admin/menus?error=dates');
        }

        $station = StationModel::findById($stationId);
        if (!$station || (int)$station['admin_id'] !== (int)$user['id']) {
            http_response_code(403);
            echo 'אין הרשאה.';
            return;
        }

        $menuId = MenuService::createMenuWithDraft($stationId, $start, $end);
        AuditService::log((int)$user['id'], 'menu_create', 'menu', (string)$menuId, null, ['station_id' => $stationId]);
        $this->redirect('/admin/menus/' . $menuId);
    }

    public function editMenu(string $id): void
    {
        $user = AuthService::currentUser();
        $menuId = (int)$id;
        $data = MenuService::getMenuWithDraft($menuId, (int)$user['id']);
        $station = StationModel::findById((int)$data['menu']['station_id']);
        if (!$station || (int)$station['admin_id'] !== (int)$user['id']) {
            http_response_code(403);
            echo 'אין הרשאה.';
            return;
        }
        $versions = MenuVersionModel::listByMenu($menuId);

        $this->render('admin/menu_edit', [
            'title' => 'עריכת תפריט',
            'menu' => $data['menu'],
            'draft' => $data['draft'],
            'items' => $data['items'],
            'dishes' => $data['dishes'],
            'versions' => $versions,
        ]);
    }

    public function saveMenu(string $id): void
    {
        $user = AuthService::currentUser();
        $menuId = (int)$id;
        $menu = MenuService::getMenuWithDraft($menuId, (int)$user['id']);
        $station = StationModel::findById((int)$menu['menu']['station_id']);
        if (!$station || (int)$station['admin_id'] !== (int)$user['id']) {
            http_response_code(403);
            echo 'אין הרשאה.';
            return;
        }
        $draftId = (int)$menu['draft']['id'];

        $items = [];
        foreach ($_POST['dish_id'] ?? [] as $index => $dishId) {
            $dishId = (int)$dishId;
            $planned = (int)($_POST['planned_portions'][$index] ?? 0);
            if ($dishId > 0 && $planned > 0) {
                $items[] = ['dish_id' => $dishId, 'planned_portions' => $planned];
            }
        }

        MenuService::saveDraftItems($draftId, $items);
        AuditService::log((int)$user['id'], 'menu_edit', 'menu_version', (string)$draftId, null, ['items' => count($items)]);
        $this->redirect('/admin/menus/' . $menuId);
    }

    public function publishMenu(string $id): void
    {
        $user = AuthService::currentUser();
        $menuId = (int)$id;
        $menu = MenuService::getMenuWithDraft($menuId, (int)$user['id']);
        $station = StationModel::findById((int)$menu['menu']['station_id']);
        if (!$station || (int)$station['admin_id'] !== (int)$user['id']) {
            http_response_code(403);
            echo 'אין הרשאה.';
            return;
        }

        try {
            MenuService::publish($menuId, (int)$user['id']);
            AuditService::log((int)$user['id'], 'menu_publish', 'menu', (string)$menuId, null, null);
            $this->redirect('/admin/menus/' . $menuId);
        } catch (\Throwable $e) {
            $this->redirect('/admin/menus/' . $menuId . '?error=1');
        }
    }

    public function reports(): void
    {
        $user = AuthService::currentUser();
        $filters = [
            'station_id' => $_GET['station_id'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
        ];
        $reports = ReportService::listReportsByAdmin((int)$user['id'], $filters);
        $stations = StationModel::listByAdmin((int)$user['id']);
        $this->render('admin/reports', [
            'title' => 'דוחות',
            'reports' => $reports,
            'stations' => $stations,
            'filters' => $filters,
        ]);
    }

    public function editReport(string $id): void
    {
        $user = AuthService::currentUser();
        $report = DailyReportModel::findById((int)$id);
        if (!$report) {
            http_response_code(404);
            echo 'דוח לא נמצא.';
            return;
        }
        $station = StationModel::findById((int)$report['station_id']);
        if (!$station || (int)$station['admin_id'] !== (int)$user['id']) {
            http_response_code(403);
            echo 'אין הרשאה.';
            return;
        }
        $wasteItems = DailyWasteItemModel::listByReport((int)$report['id']);
        $menuItems = MenuItemModel::listByMenuVersion((int)$report['menu_version_id']);
        $settings = SettingsService::getAll();
        $stepGrams = (int)($settings['weight_step_grams'] ?? 100);
        $this->render('admin/report_edit', [
            'title' => 'עריכת דוח',
            'report' => $report,
            'menu_items' => $menuItems,
            'waste_items' => $wasteItems,
            'waste_reasons' => WasteReasonModel::listActive(),
            'step_kg' => $stepGrams / 1000,
        ]);
    }

    public function updateReport(string $id): void
    {
        $user = AuthService::currentUser();
        $report = DailyReportModel::findById((int)$id);
        if (!$report) {
            http_response_code(404);
            echo 'דוח לא נמצא.';
            return;
        }
        $station = StationModel::findById((int)$report['station_id']);
        if (!$station || (int)$station['admin_id'] !== (int)$user['id']) {
            http_response_code(403);
            echo 'אין הרשאה.';
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
            ReportService::saveDailyReport((int)$report['station_id'], $report['date'], (int)$report['menu_version_id'], $headcount, $waste, $comment, (int)$user['id'], $user['role']);
            AuditService::log((int)$user['id'], 'daily_report_edit', 'daily_report', (string)$report['id'], null, ['station_id' => $report['station_id']]);
            $this->redirect('/admin/reports');
        } catch (\Throwable $e) {
            $this->redirect('/admin/reports?error=1');
        }
    }

    public function planner(): void
    {
        $user = AuthService::currentUser();
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $data = PlannerService::compute((int)$user['id'], $dateFrom, $dateTo);

        if (($_GET['export'] ?? '') === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="planner.csv"');
            echo "ingredient_id,name_he,unit,total_qty\n";
            foreach ($data as $row) {
                echo $row['ingredient_id'] . ',' . $row['name_he'] . ',' . $row['unit'] . ',' . $row['total_qty'] . "\n";
            }
            return;
        }

        $this->render('admin/planner', [
            'title' => 'מתכנן ייצור',
            'data' => $data,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
    }

    public function audit(): void
    {
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
        ];
        $user = AuthService::currentUser();
        $stationUsers = UserModel::listStationUsersByAdmin((int)$user['id']);
        $actorIds = array_merge([(int)$user['id']], array_map(fn($u) => (int)$u['id'], $stationUsers));
        $logs = AuditLogModel::list($filters, $actorIds);
        $this->render('admin/audit', [
            'title' => 'לוג פעולות',
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }
}
