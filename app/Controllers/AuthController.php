<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Services\AuditService;
use App\Core\Logger;

class AuthController extends BaseController
{
    public function showLogin(): void
    {
        if (AuthService::currentUser()) {
            $this->redirect(AuthService::homePath());
        }
        $this->render('auth/login', [
            'title' => 'התחברות',
        ]);
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->render('auth/login', [
                'title' => 'התחברות',
                'error' => 'נא למלא אימייל וסיסמה',
            ]);
            return;
        }

        $user = AuthService::attempt($email, $password);
        if (!$user) {
            Logger::info('Login failed', ['email' => $email]);
            AuditService::log(null, 'login_failed', 'user', $email, null, null);
            $this->render('auth/login', [
                'title' => 'התחברות',
                'error' => 'פרטי התחברות שגויים',
            ]);
            return;
        }

        AuditService::log($user['id'], 'login_success', 'user', (string)$user['id'], null, null);

        if ((int)$user['must_change_password'] === 1) {
            $this->redirect('/change-password');
        }

        $this->redirect(AuthService::homePath());
    }

    public function logout(): void
    {
        AuthService::logout();
        $this->redirect('/login');
    }

    public function showChangePassword(): void
    {
        $this->render('auth/change_password', [
            'title' => 'החלפת סיסמה',
        ]);
    }

    public function changePassword(): void
    {
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if ($password === '' || $password !== $password2 || strlen($password) < 8) {
            $this->render('auth/change_password', [
                'title' => 'החלפת סיסמה',
                'error' => 'סיסמה לא תקינה',
            ]);
            return;
        }

        $user = AuthService::currentUser();
        if (!$user) {
            $this->redirect('/login');
        }

        AuthService::updatePassword((int)$user['id'], $password);
        AuditService::log($user['id'], 'password_change', 'user', (string)$user['id'], null, null);
        $this->redirect(AuthService::homePath());
    }
}