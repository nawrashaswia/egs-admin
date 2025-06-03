<?php

namespace App\Services;

use App\Core\App;
use App\Core\DB;
use App\Core\ViewRenderer;
use PDOException;

class AuthService
{
    public static function handleRoot()
    {
        if (App::get('auth')->check()) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    public static function showLogin()
    {
        ViewRenderer::render('pages/login', [
            'layout' => false,
            'title'  => 'Login'
        ]);
    }

    public static function submitLogin(): void
    {
        error_log("DEBUG: AuthService::submitLogin CALLED");
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        error_log("🔐 Submitted: $username");

        try {
            $pdo = DB::connect();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                App::get('auth')->login($user);
                error_log("🎉 Login successful: $username");
                header('Location: ' . BASE_URL . '/dashboard');
                exit;
            }

            error_log("❌ Invalid credentials");
            $_SESSION['login_error'] = 'Invalid username or password.';
            header('Location: ' . BASE_URL . '/login');
            exit;

        } catch (PDOException $e) {
            error_log("❌ DB error: " . $e->getMessage());
            $_SESSION['login_error'] = 'Database error.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function logout(): void
    {
        error_log("DEBUG: AuthService::logout CALLED");
        App::get('auth')->logout();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
