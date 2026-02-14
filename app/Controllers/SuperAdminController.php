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
            'title' => '????? ?????',
            'admins' => $admins,
        ]);
    }

    public function createAdmin(): void
    {
        $email = trim($_POST['email'] ?? '');
        $tempPassword = trim($_POST['temp_password'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $workHours = trim($_POST['work_hours'] ?? '');
        if ($email === '' || $tempPassword === '' || $firstName === '' || $lastName === '' || $phone === '' || $workHours === '') {
            $this->redirect('/sa/admins');
        }
        $hash = password_hash($tempPassword, PASSWORD_BCRYPT);
        $id = UserModel::create([
            'email' => $email,
            'password_hash' => $hash,
            'role' => 'ADMIN',
            'admin_id' => null,
            'station_id' => null,
            'job_title' => null,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'work_hours' => $workHours,
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
            'title' => '?????',
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

    public function updateStation(): void
    {
        $user = AuthService::currentUser();
        $stationId = (int)($_POST['station_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $adminId = (int)($_POST['admin_id'] ?? 0);
        $isActive = (int)($_POST['is_active'] ?? 1);

        if ($stationId < 1 || $name === '' || $adminId < 1) {
            $this->redirect('/sa/stations');
        }

        $before = StationModel::findById($stationId);
        if (!$before) {
            $this->redirect('/sa/stations');
        }

        StationModel::update($stationId, $name, $adminId, $isActive === 1 ? 1 : 0);
        $after = StationModel::findById($stationId);
        AuditService::log((int)$user['id'], 'station_update', 'station', (string)$stationId, $before, $after ?: null);
        $this->redirect('/sa/stations');
    }

    public function deleteStation(): void
    {
        $user = AuthService::currentUser();
        $stationId = (int)($_POST['station_id'] ?? 0);
        if ($stationId < 1) {
            $this->redirect('/sa/stations');
        }

        $before = StationModel::findById($stationId);
        if (!$before) {
            $this->redirect('/sa/stations');
        }

        try {
            StationModel::delete($stationId);
            AuditService::log((int)$user['id'], 'station_delete', 'station', (string)$stationId, $before, null);
        } catch (\Throwable $e) {
            AuditService::log((int)$user['id'], 'station_delete_failed', 'station', (string)$stationId, $before, ['error' => $e->getMessage()]);
        }
        $this->redirect('/sa/stations');
    }

    public function users(): void
    {
        $users = UserModel::listAllUsers();
        $admins = UserModel::listAdmins();
        $stations = StationModel::listAll();
        $this->render('sa/users', [
            'title' => '???????',
            'users' => $users,
            'admins' => $admins,
            'stations' => $stations,
            'job_titles' => UserModel::jobTitles(),
        ]);
    }

    public function createUser(): void
    {
        $actor = AuthService::currentUser();
        $email = trim($_POST['email'] ?? '');
        $tempPassword = trim($_POST['temp_password'] ?? '');
        $role = $_POST['role'] ?? 'STATION_USER';
        $adminId = (int)($_POST['admin_id'] ?? 0);
        $stationId = (int)($_POST['station_id'] ?? 0);
        $jobTitle = trim($_POST['job_title'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $workHours = trim($_POST['work_hours'] ?? '');

        if ($email === '' || $tempPassword === '' || !in_array($role, ['SUPERADMIN', 'ADMIN', 'STATION_USER'], true) || $firstName === '' || $lastName === '' || $phone === '' || $workHours === '') {
            $this->redirect('/sa/users');
        }

        $data = [
            'email' => $email,
            'password_hash' => password_hash($tempPassword, PASSWORD_BCRYPT),
            'role' => $role,
            'admin_id' => null,
            'station_id' => null,
            'job_title' => null,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'work_hours' => $workHours,
            'must_change_password' => 1,
            'is_active' => 1,
        ];

        if ($role === 'STATION_USER') {
            if (!in_array($jobTitle, UserModel::jobTitles(), true)) {
                $this->redirect('/sa/users');
            }
            $data['admin_id'] = $adminId > 0 ? $adminId : null;
            $data['station_id'] = $stationId > 0 ? $stationId : null;
            $data['job_title'] = $jobTitle;
        }

        $newId = UserModel::create($data);
        AuditService::log((int)$actor['id'], 'user_create', 'user', (string)$newId, null, ['email' => $email, 'role' => $role]);
        $this->redirect('/sa/users');
    }

    public function updateUser(): void
    {
        $actor = AuthService::currentUser();
        $userId = (int)($_POST['user_id'] ?? 0);
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? '';
        $adminId = (int)($_POST['admin_id'] ?? 0);
        $stationId = (int)($_POST['station_id'] ?? 0);
        $jobTitle = trim($_POST['job_title'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $workHours = trim($_POST['work_hours'] ?? '');
        $isActive = (int)($_POST['is_active'] ?? 1);

        if ($userId < 1 || $email === '' || !in_array($role, ['SUPERADMIN', 'ADMIN', 'STATION_USER'], true) || $firstName === '' || $lastName === '' || $phone === '' || $workHours === '') {
            $this->redirect('/sa/users');
        }

        $before = UserModel::findById($userId);
        if (!$before) {
            $this->redirect('/sa/users');
        }

        $payload = [
            'email' => $email,
            'role' => $role,
            'admin_id' => null,
            'station_id' => null,
            'job_title' => null,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'work_hours' => $workHours,
            'is_active' => $isActive === 1 ? 1 : 0,
        ];
        if ($role === 'STATION_USER') {
            if (!in_array($jobTitle, UserModel::jobTitles(), true)) {
                $this->redirect('/sa/users');
            }
            $payload['admin_id'] = $adminId > 0 ? $adminId : null;
            $payload['station_id'] = $stationId > 0 ? $stationId : null;
            $payload['job_title'] = $jobTitle;
        }

        UserModel::updateUser($userId, $payload);
        $after = UserModel::findById($userId);
        AuditService::log((int)$actor['id'], 'user_update', 'user', (string)$userId, $before, $after ?: null);
        $this->redirect('/sa/users');
    }

    public function deleteUser(): void
    {
        $actor = AuthService::currentUser();
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId < 1 || $userId === (int)$actor['id']) {
            $this->redirect('/sa/users');
        }

        $before = UserModel::findById($userId);
        if (!$before) {
            $this->redirect('/sa/users');
        }

        try {
            UserModel::delete($userId);
            AuditService::log((int)$actor['id'], 'user_delete', 'user', (string)$userId, $before, null);
        } catch (\Throwable $e) {
            AuditService::log((int)$actor['id'], 'user_delete_failed', 'user', (string)$userId, $before, ['error' => $e->getMessage()]);
        }
        $this->redirect('/sa/users');
    }

    public function settings(): void
    {
        $settings = SettingsService::getAll();
        $this->render('sa/settings', [
            'title' => '??????',
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
            'title' => '??? ?????',
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }
}
