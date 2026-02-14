<?php
use App\Core\ErrorHandler;
use App\Router;
use App\Controllers\AuthController;
use App\Controllers\StationController;
use App\Controllers\AdminController;
use App\Controllers\SuperAdminController;

require __DIR__ . '/../app/Core/Helpers.php';

$config = require __DIR__ . '/../app/config.php';

if (!empty($config['app']['timezone'])) {
    date_default_timezone_set($config['app']['timezone']);
}

session_name($config['session']['name']);
$cookieParams = session_get_cookie_params();
$secure = $config['session']['secure'];
$samesite = $config['session']['samesite'] ?? 'Lax';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => $cookieParams['path'],
    'domain' => $cookieParams['domain'],
    'secure' => $secure,
    'httponly' => true,
    'samesite' => $samesite,
]);

session_start();

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

ErrorHandler::register();

$router = new Router();

$auth = new AuthController();
$station = new StationController();
$admin = new AdminController();
$sa = new SuperAdminController();

$router->get('/', function (): void {
    header('Location: ' . app_url('/login'));
    exit;
});

$router->get('/login', [$auth, 'showLogin']);
$router->post('/login', [$auth, 'login']);
$router->get('/logout', [$auth, 'logout'], ['auth' => true]);
$router->get('/change-password', [$auth, 'showChangePassword'], ['auth' => true]);
$router->post('/change-password', [$auth, 'changePassword'], ['auth' => true]);

$router->get('/station/today', [$station, 'today'], ['auth' => true, 'roles' => ['STATION_USER']]);
$router->post('/station/today', [$station, 'submitToday'], ['auth' => true, 'roles' => ['STATION_USER']]);
$router->get('/station/history', [$station, 'history'], ['auth' => true, 'roles' => ['STATION_USER']]);

$router->get('/admin/dashboard', [$admin, 'dashboard'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);
$router->get('/admin/stations', [$admin, 'stations'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);
$router->post('/admin/stations', [$admin, 'updateStation'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);
$router->get('/admin/users', [$admin, 'users'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);
$router->post('/admin/users/create', [$admin, 'createStationUser'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);
$router->post('/admin/users/reset', [$admin, 'resetUserPassword'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);
$router->post('/admin/users/toggle', [$admin, 'toggleUser'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);

$router->get('/admin/menus', [$admin, 'menus'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);
$router->post('/admin/menus', [$admin, 'createMenu'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);
$router->get('/admin/menus/{id}', [$admin, 'editMenu'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);
$router->post('/admin/menus/{id}', [$admin, 'saveMenu'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);
$router->post('/admin/menus/{id}/publish', [$admin, 'publishMenu'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);

$router->get('/admin/reports', [$admin, 'reports'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);
$router->get('/admin/reports/{id}', [$admin, 'editReport'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);
$router->post('/admin/reports/{id}', [$admin, 'updateReport'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);
$router->get('/admin/planner', [$admin, 'planner'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);
$router->get('/admin/audit', [$admin, 'audit'], ['auth' => true, 'roles' => ['ADMIN', 'STATION_MANAGER']]);

$router->get('/sa/admins', [$sa, 'admins'], ['auth' => true, 'roles' => ['SUPERADMIN']]);
$router->post('/sa/admins', [$sa, 'createAdmin'], ['auth' => true, 'roles' => ['SUPERADMIN']]);
$router->get('/sa/stations', [$sa, 'stations'], ['auth' => true, 'roles' => ['SUPERADMIN']]);
$router->post('/sa/stations', [$sa, 'createStation'], ['auth' => true, 'roles' => ['SUPERADMIN']]);
$router->post('/sa/stations/update', [$sa, 'updateStation'], ['auth' => true, 'roles' => ['SUPERADMIN']]);
$router->post('/sa/stations/delete', [$sa, 'deleteStation'], ['auth' => true, 'roles' => ['SUPERADMIN']]);
$router->get('/sa/users', [$sa, 'users'], ['auth' => true, 'roles' => ['SUPERADMIN', 'DISTRICT_MANAGER', 'AREA_MANAGER']]);
$router->post('/sa/users/create', [$sa, 'createUser'], ['auth' => true, 'roles' => ['SUPERADMIN', 'DISTRICT_MANAGER', 'AREA_MANAGER']]);
$router->post('/sa/users/update', [$sa, 'updateUser'], ['auth' => true, 'roles' => ['SUPERADMIN', 'DISTRICT_MANAGER', 'AREA_MANAGER']]);
$router->post('/sa/users/delete', [$sa, 'deleteUser'], ['auth' => true, 'roles' => ['SUPERADMIN', 'DISTRICT_MANAGER', 'AREA_MANAGER']]);
$router->get('/sa/settings', [$sa, 'settings'], ['auth' => true, 'roles' => ['SUPERADMIN']]);
$router->post('/sa/settings', [$sa, 'updateSettings'], ['auth' => true, 'roles' => ['SUPERADMIN']]);
$router->get('/sa/audit', [$sa, 'audit'], ['auth' => true, 'roles' => ['SUPERADMIN']]);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
