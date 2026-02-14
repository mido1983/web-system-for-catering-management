<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Services\SettingsService;
use App\Services\AuditService;
use App\Models\UserModel;
use App\Models\StationModel;
use App\Models\AuditLogModel;

class SuperAdminController extends BaseController
{
    public function admins(): void
    {
        $admins = UserModel::listAdmins();
        $this->render('sa/admins', [
            'title' => 'מנהלי מערכת',
            'admins' => $admins,
        ]);
    }

    public function createAdmin(): void
    {
        $email = trim($_POST['email'] ?? '');
        $tempPassword = trim($_POST['temp_password'] ?? '');
        if ($email === '' || $tempPassword === '') {
            $this->redirect('/sa/admins');
        }
        $hash = password_hash($tempPassword, PASSWORD_BCRYPT);
        $id = UserModel::create([
            'email' => $email,
            'password_hash' => $hash,
            'role' => 'ADMIN',
            'admin_id' => null,
            'station_id' => null,
            'must_change_password' => 1,
            'is_active' => 1,
        ]);
        AuditService::log((int)AuthService::currentUser()['id'], 'admin_create', 'user', (string)$id, null, ['email' => $email]);
        $this->redirect('/sa/admins');
    }

    public function stations(): void
    {
        $stations = StationModel::listAll();
        $admins = UserModel::listAdmins();
        $this->render('sa/stations', [
            'title' => 'תחנות',
            'stations' => $stations,
            'admins' => $admins,
        ]);
    }

    public function createStation(): void
    {
        $name = trim($_POST['name'] ?? '');
        $adminId = (int)($_POST['admin_id'] ?? 0);
        if ($name === '' || $adminId < 1) {
            $this->redirect('/sa/stations');
        }
        $id = StationModel::create([
            'name' => $name,
            'admin_id' => $adminId,
            'is_active' => 1,
        ]);
        AuditService::log((int)AuthService::currentUser()['id'], 'station_create', 'station', (string)$id, null, ['name' => $name]);
        $this->redirect('/sa/stations');
    }

    public function users(): void
    {
        $users = UserModel::listAllUsers();
        $this->render('sa/users', [
            'title' => 'משתמשים',
            'users' => $users,
        ]);
    }

    public function settings(): void
    {
        $settings = SettingsService::getAll();
        $this->render('sa/settings', [
            'title' => 'הגדרות',
            'settings' => $settings,
        ]);
    }

    public function updateSettings(): void
    {
        $user = AuthService::currentUser();
        foreach (['deadline_time', 'polling_seconds', 'weight_step_grams', 'app_name_he', 'support_phone'] as $key) {
            if (isset($_POST[$key])) {
                SettingsService::update($key, trim($_POST[$key]), (int)$user['id']);
            }
        }
        AuditService::log((int)$user['id'], 'settings_update', 'settings', 'settings', null, $_POST);
        $this->redirect('/sa/settings');
    }

    public function audit(): void
    {
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
        ];
        $logs = AuditLogModel::list($filters);
        $this->render('sa/audit', [
            'title' => 'לוג מערכת',
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }
}