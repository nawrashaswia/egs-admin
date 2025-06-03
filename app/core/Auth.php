<?php

namespace App\Core;

/**
 * Simple session-based authentication manager
 */
class Auth
{
    /**
     * Holds session data (assigned by reference in constructor)
     *
     * @var array
     */
    protected $session;

    public function __construct()
    {
        $this->session = &$_SESSION;
    }

    public function check(): bool
    {
        return isset($this->session['user_id']);
    }

    public function id(): ?int
    {
        return $this->session['user_id'] ?? null;
    }

    public function user(): ?array
    {
        return $this->check() ? [
            'id'         => $this->session['user_id'],
            'username'   => $this->session['username'] ?? null,
            'full_name'  => $this->session['full_name'] ?? null,
            'avatar'     => $this->session['avatar'] ?? null,
            'role'       => $this->session['role'] ?? null,
            'login_time' => $this->session['login_time'] ?? null
        ] : null;
    }

    public function login(array $user): void
    {
        error_log("DEBUG: login() called (raw PHP log)");
        try {
            \App\Core\Logger::trigger('DEBUG: login() called', [], 'DEBUG', 'system');
        } catch (\Throwable $e) {
            error_log("LOGGER ERROR (login): " . $e->getMessage());
        }
        $this->session['user_id']    = (int)($user['id'] ?? 0);
        $this->session['username']   = $user['username'] ?? null;
        $this->session['full_name']  = $user['full_name'] ?? $user['username'] ?? null;
        $this->session['avatar']     = $user['avatar'] ?? null;
        $this->session['role']       = $user['role'] ?? 'User';
        $this->session['login_time'] = time();

        // Always log to normal logs
        try {
            \App\Core\Logger::trigger("🔐 User login", ['user' => $this->session['username']], 'INFO', 'system');
        } catch (\Throwable $e) {
            error_log("LOGGER ERROR (login event): " . $e->getMessage());
        }
        // If trace mode/session is active, also log as trace
        if (\App\Core\TraceManager::isTracing()) {
            try {
                \App\Core\Logger::trigger("🔐 User login", ['user' => $this->session['username']], 'INFO', 'trace');
            } catch (\Throwable $e) {
                error_log("LOGGER ERROR (login trace): " . $e->getMessage());
            }
        }
    }

    public function logout(): void
    {
        error_log("DEBUG: logout() called (raw PHP log)");
        try {
            \App\Core\Logger::trigger('DEBUG: logout() called', [], 'DEBUG', 'system');
        } catch (\Throwable $e) {
            error_log("LOGGER ERROR (logout): " . $e->getMessage());
        }
        if (!empty($this->session['username'])) {
            // Always log to normal logs
            try {
                \App\Core\Logger::trigger("🚪 User logout", ['user' => $this->session['username']], 'INFO', 'system');
            } catch (\Throwable $e) {
                error_log("LOGGER ERROR (logout event): " . $e->getMessage());
            }
            // If trace mode/session is active, also log as trace
            if (\App\Core\TraceManager::isTracing()) {
                try {
                    \App\Core\Logger::trigger("🚪 User logout", ['user' => $this->session['username']], 'INFO', 'trace');
                } catch (\Throwable $e) {
                    error_log("LOGGER ERROR (logout trace): " . $e->getMessage());
                }
            }
        }
        session_unset();
        session_destroy();
    }

    public function hasRole(string $role): bool
    {
        return strtolower($this->session['role'] ?? '') === strtolower($role);
    }

    public function requireLogin(string $redirectTo = '/login'): void
    {
        if (!$this->check()) {
            header("Location: $redirectTo");
            exit;
        }
    }

    /**
     * Return simplified user context for logs
     */
    public function current(): array
    {
        return [
            'id'       => $this->session['user_id'] ?? null,
            'username' => $this->session['username'] ?? 'system',
            'role'     => $this->session['role'] ?? 'guest',
        ];
    }
}
