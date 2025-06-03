<?php

namespace App\Core;

use App\Core\App;
use App\Core\Auth;
use App\Core\DB;
use App\Core\ViewRenderer;
use App\Core\Logger;
use App\Core\TraceManager;
use App\Helpers\Core\ErrorHandler;
use App\Helpers\General_Module\LogManager\LogContextBuilder;
use Throwable;

class AppKernel
{
    private static bool $booted = false;

    public static function boot(): void
    {
        if (self::$booted) return;
        self::$booted = true;

        try {
            // 1. Define paths
            define('BASE_PATH', realpath(__DIR__ . '/../..'));
            define('APP_PATH', BASE_PATH . '/app');
            define('CORE_PATH', APP_PATH . '/core');
            define('HELPERS_PATH', APP_PATH . '/helpers');
            define('MODULES_PATH', APP_PATH . '/modules');
            define('CONFIG_PATH', BASE_PATH . '/config');
            define('PUBLIC_PATH', BASE_PATH . '/public');
            define('VIEWS_PATH', APP_PATH . '/views');
            define('STORAGE_PATH', BASE_PATH . '/storage');
            define('LOGS_PATH', BASE_PATH . '/logs');

            // 1.5 Preload tracing statement manually
            require_once HELPERS_PATH . '/core/TracingDBStatement.php';

            // 2. Register universal lowercase-friendly autoloader
            spl_autoload_register(function ($class) {
                $prefix = 'App\\';
                $baseDir = APP_PATH . '/';

                if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;

                $relativeClass = substr($class, strlen($prefix));
                $segments = explode('\\', $relativeClass);
                $pathParts = [];

                foreach ($segments as $i => $segment) {
                    // Keep last segment as-is (class file name)
                    if ($i === count($segments) - 1) {
                        $pathParts[] = $segment . '.php';
                    } else {
                        $pathParts[] = strtolower($segment);
                    }
                }

                $fullPath = $baseDir . implode('/', $pathParts);

                if (file_exists($fullPath)) {
                    require_once $fullPath;
                }
            });

            // 3. Load config files
            $appConfig     = require CONFIG_PATH . '/app.php';
            $sessionConfig = require CONFIG_PATH . '/session.php';
            $dbConfig      = require CONFIG_PATH . '/database.php';

            date_default_timezone_set($appConfig['timezone'] ?? 'UTC');
            App::set('config', $appConfig);
            App::set('session_config', $sessionConfig);
            App::set('db_config', $dbConfig);

            // 4. Global config helper
            if (!function_exists('config')) {
                function config(string $key, mixed $default = null): mixed {
                    $config = App::get('config');
                    foreach (explode('.', $key) as $segment) {
                        if (is_array($config) && array_key_exists($segment, $config)) {
                            $config = $config[$segment];
                        } else {
                            return $default;
                        }
                    }
                    return $config;
                }
            }

            // 5. Core constants
            defined('APP_NAME')   || define('APP_NAME', $appConfig['app_name'] ?? 'EGS-ADMIN');
            defined('BASE_URL')   || define('BASE_URL', $appConfig['base_url'] ?? '/');
            defined('DEBUG_MODE') || define('DEBUG_MODE', $appConfig['debug'] ?? false);
            defined('CHARSET')    || define('CHARSET', $appConfig['charset'] ?? 'UTF-8');

            // 6. PHP settings
            ini_set('default_charset', CHARSET);
            ini_set('display_errors', DEBUG_MODE ? '1' : '0');
            error_reporting(DEBUG_MODE ? E_ALL : 0);

            // 7. Start session
            if (!session_id() && ($sessionConfig['auto_start'] ?? true)) {
                session_name($sessionConfig['session_name'] ?? 'EGSESSID');
                session_set_cookie_params([
                    'lifetime' => $sessionConfig['timeout'] ?? 3600,
                    'path' => '/',
                    'domain' => '',
                    'secure' => $sessionConfig['cookie_secure'] ?? false,
                    'httponly' => $sessionConfig['cookie_httponly'] ?? true,
                    'samesite' => $sessionConfig['cookie_samesite'] ?? 'Lax'
                ]);
                session_start();

                if (isset($sessionConfig['timeout'])) {
                    if (isset($_SESSION['__last_active']) && time() - $_SESSION['__last_active'] > $sessionConfig['timeout']) {
                        session_unset();
                        session_destroy();
                        session_start();
                    }
                    $_SESSION['__last_active'] = time();
                }
            }

            // 8. Trace logic (auto)
            if (TraceManager::isTracing()) {
                Logger::trigger("Trace mode booted", LogContextBuilder::enrich([
                    'source' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['file'] ?? 'kernel'
                ]), 'DEBUG', 'trace');

                if (!TraceManager::isTraceSessionActive()) {
                    Logger::startConstructionTrace('bootstrap', 'Fallback auto-trace session started by Kernel');
                }
            }

            // 9. Register core services
            App::set('auth', new Auth());
            App::set('db', DB::connect());

            Logger::trigger('🧠 DB booted using TracingDBPDO',
            LogContextBuilder::enrich([
                'dsn'    => $dbConfig['driver'] . '://' . $dbConfig['host'] . '/' . $dbConfig['name'],
                'tag'    => 'boot.db', // ✨ for deduplication
                'module' => (function() {
                    try {
                        if (class_exists('App\\Helpers\\Core\\HiLoSequence')) {
                            return \App\Helpers\Core\HiLoSequence::detectModuleName();
                        }
                    } catch (\Throwable $e) {}
                    return 'appkernel';
                })()
            ]),
            'DEBUG',
            TraceManager::isTracing() ? 'trace' : 'system',
            'dev',
            'short'
        );

            // 10. View context
            App::set('view.context', [
                'ref'    => $GLOBALS['ref'] ?? null,
                'user'   => $_SESSION['user_name'] ?? 'Guest',
                'date'   => date('Y-m-d'),
                'config' => $appConfig,
            ]);

            // 11. Error handling
            ErrorHandler::init();

            error_log("[Kernel] Boot completed successfully.");
        } catch (Throwable $e) {
            error_log("[Kernel] Boot failed: " . $e->getMessage());
            require_once __DIR__ . '/../helpers/core/ErrorHandler.php';
            ErrorHandler::serverError("AppKernel boot failed", 'BOOT_FAIL', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'message' => $e->getMessage()
            ]);
        }
    }
}
