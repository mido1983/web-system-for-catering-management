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
        $workHours = $this->resolveWorkHours($_POST);
        if ($email === '' || $tempPassword === '' || $firstName === '' || $lastName === '' || $phone === '' || $workHours === '' || !$this->isValidWorkHours($workHours)) {
            $this->redirect('/sa/admins');
        }
        $hash = password_hash($tempPassword, PASSWORD_BCRYPT);
        $id = UserModel::create([
            'email' => $email,
            'password_hash' => $hash,
            'role' => 'STATION_MANAGER',
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
        $supplyTargets = [];
        foreach ($stations as $station) {
            $sid = (int)$station['id'];
            $supplyTargets[$sid] = StationModel::listSupplyTargets($sid);
        }
        $this->render('sa/stations', [
            'title' => '?????',
            'stations' => $stations,
            'admins' => $admins,
            'supply_targets' => $supplyTargets,
        ]);
    }

    public function createStation(): void
    {
        $name = trim($_POST['name'] ?? '');
        $adminId = (int)($_POST['admin_id'] ?? 0);
        $isCookingKitchen = (int)($_POST['is_cooking_kitchen'] ?? 0);
        $targetStationIds = $_POST['target_station_ids'] ?? [];
        if ($name === '' || $adminId < 1) {
            $this->redirect('/sa/stations');
        }
        $manager = UserModel::findById($adminId);
        if (!$manager || !in_array(UserModel::normalizedManagerRole((string)$manager['role']), ['STATION_MANAGER'], true)) {
            $this->redirect('/sa/stations');
        }
        $id = StationModel::create([
            'name' => $name,
            'admin_id' => $adminId,
            'is_active' => 1,
            'is_cooking_kitchen' => $isCookingKitchen === 1 ? 1 : 0,
        ]);
        if ($isCookingKitchen === 1) {
            $targetIds = array_map('intval', is_array($targetStationIds) ? $targetStationIds : []);
            StationModel::replaceSupplyTargets($id, $targetIds);
        }
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
        $isCookingKitchen = (int)($_POST['is_cooking_kitchen'] ?? 0);
        $targetStationIds = $_POST['target_station_ids'] ?? [];

        if ($stationId < 1 || $name === '' || $adminId < 1) {
            $this->redirect('/sa/stations');
        }
        $manager = UserModel::findById($adminId);
        if (!$manager || !in_array(UserModel::normalizedManagerRole((string)$manager['role']), ['STATION_MANAGER'], true)) {
            $this->redirect('/sa/stations');
        }

        $before = StationModel::findById($stationId);
        if (!$before) {
            $this->redirect('/sa/stations');
        }

        StationModel::update($stationId, $name, $adminId, $isActive === 1 ? 1 : 0, $isCookingKitchen === 1 ? 1 : 0);
        if ($isCookingKitchen === 1) {
            $targetIds = array_map('intval', is_array($targetStationIds) ? $targetStationIds : []);
            StationModel::replaceSupplyTargets($stationId, $targetIds);
        } else {
            StationModel::replaceSupplyTargets($stationId, []);
        }
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
        $actor = AuthService::currentUser();
        $users = UserModel::listAllUsers();
        $admins = UserModel::listAdmins();
        $stations = StationModel::listAll();
        $roleManagers = [
            'superadmins' => UserModel::listManagersByRole('SUPERADMIN'),
            'districts' => UserModel::listManagersByRole('DISTRICT_MANAGER'),
            'areas' => UserModel::listManagersByRole('AREA_MANAGER'),
            'stations' => array_merge(
                UserModel::listManagersByRole('STATION_MANAGER'),
                UserModel::listManagersByRole('ADMIN')
            ),
        ];
        $this->render('sa/users', [
            'title' => '???????',
            'users' => $users,
            'admins' => $admins,
            'stations' => $stations,
            'job_titles' => UserModel::jobTitles(),
            'role_managers' => $roleManagers,
            'actor_role' => (string)($actor['role'] ?? ''),
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
        $workHours = $this->resolveWorkHours($_POST);

        if ($email === '' || $tempPassword === '' || !in_array($role, UserModel::userAssignableRoles(), true) || $firstName === '' || $lastName === '' || $phone === '' || $workHours === '' || !$this->isValidWorkHours($workHours)) {
            $this->redirect('/sa/users');
        }
        if (!$this->canActorManageRole((string)$actor['role'], $role)) {
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
            if ($stationId < 1 || !in_array($jobTitle, UserModel::jobTitles(), true) || !$this->validateRoleDependency($role, $adminId)) {
                $this->redirect('/sa/users');
            }
            $data['admin_id'] = $adminId > 0 ? $adminId : null;
            $data['station_id'] = $stationId > 0 ? $stationId : null;
            $data['job_title'] = $jobTitle;
        } elseif ($role !== 'SUPERADMIN') {
            if (!$this->validateRoleDependency($role, $adminId)) {
                $this->redirect('/sa/users');
            }
            $data['admin_id'] = $adminId > 0 ? $adminId : null;
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
        $workHours = $this->resolveWorkHours($_POST);
        $isActive = (int)($_POST['is_active'] ?? 1);

        if ($userId < 1 || $email === '' || !in_array($role, UserModel::userAssignableRoles(), true) || $firstName === '' || $lastName === '' || $phone === '' || $workHours === '' || !$this->isValidWorkHours($workHours)) {
            $this->redirect('/sa/users');
        }

        $before = UserModel::findById($userId);
        if (!$before) {
            $this->redirect('/sa/users');
        }
        if (!$this->canActorManageRole((string)$actor['role'], (string)$before['role']) || !$this->canActorManageRole((string)$actor['role'], $role)) {
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
            if ($stationId < 1 || !in_array($jobTitle, UserModel::jobTitles(), true) || !$this->validateRoleDependency($role, $adminId)) {
                $this->redirect('/sa/users');
            }
            $payload['admin_id'] = $adminId > 0 ? $adminId : null;
            $payload['station_id'] = $stationId > 0 ? $stationId : null;
            $payload['job_title'] = $jobTitle;
        } elseif ($role !== 'SUPERADMIN') {
            if (!$this->validateRoleDependency($role, $adminId)) {
                $this->redirect('/sa/users');
            }
            $payload['admin_id'] = $adminId > 0 ? $adminId : null;
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
        if (!$this->canActorManageRole((string)$actor['role'], (string)$before['role'])) {
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

    private function canActorManageRole(string $actorRole, string $targetRole): bool
    {
        if ($actorRole === 'SUPERADMIN') {
            return true;
        }

        $targetRole = UserModel::normalizedManagerRole($targetRole);
        if ($actorRole === 'DISTRICT_MANAGER') {
            return in_array($targetRole, ['AREA_MANAGER', 'STATION_MANAGER', 'STATION_USER'], true);
        }
        if ($actorRole === 'AREA_MANAGER') {
            return in_array($targetRole, ['STATION_MANAGER', 'STATION_USER'], true);
        }

        return false;
    }

    private function validateRoleDependency(string $role, int $adminId): bool
    {
        $normalized = UserModel::normalizedManagerRole($role);
        $expectedParentRole = UserModel::parentRoleFor($normalized);
        if ($expectedParentRole === null) {
            return true;
        }
        if ($adminId < 1) {
            return false;
        }

        $parent = UserModel::findById($adminId);
        if (!$parent || (int)$parent['is_active'] !== 1) {
            return false;
        }

        $actualParentRole = UserModel::normalizedManagerRole((string)$parent['role']);
        if ($normalized === 'STATION_USER') {
            return in_array($actualParentRole, ['STATION_MANAGER'], true);
        }

        return $actualParentRole === $expectedParentRole;
    }

    private function resolveWorkHours(array $source): string
    {
        $start = trim((string)($source['work_start'] ?? ''));
        $end = trim((string)($source['work_end'] ?? ''));
        if ($start !== '' && $end !== '') {
            return $start . ' - ' . $end;
        }
        return trim((string)($source['work_hours'] ?? ''));
    }

    private function isValidWorkHours(string $workHours): bool
    {
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d\s-\s([01]\d|2[0-3]):[0-5]\d$/', $workHours, $m)) {
            return false;
        }
        return strcmp($m[1], $m[2]) < 0;
    }
}
