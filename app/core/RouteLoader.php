<?php

namespace App\Core;

use App\Helpers\Core\RouteHelper;
use Throwable;

class RouteLoader
{
    private static array $loggedModules = [];

    public static function loadAll(): void
    {
        // 1️⃣ Register AJAX validator globally
        $ajaxHandler = APP_PATH . '/ajax/system_module/validate.php';
        if (file_exists($ajaxHandler)) {
            RouteHelper::post('/ajax/system_module/validate', function() use ($ajaxHandler) {
                try {
                    ob_start();
                    require_once $ajaxHandler;
                    $output = ob_get_clean();

                    if (str_starts_with(trim($output), '<!DOCTYPE')) {
                        header('Content-Type: application/json');
                        echo json_encode(['status' => 'error', 'issues' => ['Internal server error']]);
                        exit;
                    }

                    header('Content-Type: application/json');
                    echo $output;
                    exit;
                } catch (Throwable $e) {
                    ob_end_clean();
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'issues' => ['Validation error: ' . $e->getMessage()]]);
                    exit;
                }
            });
        }

        // 2️⃣ Load global route map
        $globalMap = VIEWS_PATH . '/routes.map.php';
        if (file_exists($globalMap)) {
            self::loadFromMapFile($globalMap, APP_PATH);
        }

        // 3️⃣ Load each module’s route map
        foreach (array_filter(glob(MODULES_PATH . '/*'), 'is_dir') as $moduleDir) {
            $mapFile = $moduleDir . '/controllers/routes.map.php';
            if (!file_exists($mapFile)) continue;

            // ✅ Trace map file loading
            if (TraceManager::isTracing() && !in_array($moduleDir, self::$loggedModules)) {
                Logger::trigger("Loaded module route map", [
                    'module' => basename($moduleDir),
                    'file'   => $mapFile
                ], 'DEBUG', 'trace');
                self::$loggedModules[] = $moduleDir;
            }

            self::loadFromMapFile($mapFile, $moduleDir . '/controllers');
        }
    }

    private static function loadFromMapFile(string $mapFile, string $basePath): void
    {
        $routes = require $mapFile;
        if (!is_array($routes)) return;

        $base = str_replace(['\\', '//'], '/', rtrim($basePath, '/'));

        // 🔍 Load view routes
        foreach ($routes['views'] ?? [] as $path => $view) {
            $filePath = VIEWS_PATH . '/' . ltrim($view, '/');
            RouteHelper::view($path, $view, ['title' => ucfirst(basename($view, '.php'))]);

            if (TraceManager::isTracing()) {
                Logger::trigger("📄 View route registered", [
                    'path' => $path,
                    'file' => realpath($filePath) ?: $filePath,
                    'title' => basename($view),
                ], 'DEBUG', 'trace');
            }
        }

        // 🔍 Load controller routes
        foreach ($routes['controllers'] ?? [] as $r) {
            $method = strtolower($r['method'] ?? 'get');
            $routePath = $r['path'] ?? null;

            $handler = null;
            if (isset($r['handler'])) {
                $handler = is_callable($r['handler']) ? $r['handler'] : $base . '/' . ltrim($r['handler'], '/');
            } elseif (isset($r['file'])) {
                $handler = $base . '/' . ltrim($r['file'], '/');
            }

            if (!$routePath || !$handler) continue;

            if ($method === 'post') {
                RouteHelper::post($routePath, $handler);
            } else {
                RouteHelper::get($routePath, $handler);
            }

            if (TraceManager::isTracing()) {
                Logger::trigger("📦 Controller route registered", [
                    'path'   => $routePath,
                    'file'   => is_string($handler) ? realpath($handler) ?: $handler : 'inline/closure',
                    'method' => strtoupper($method)
                ], 'DEBUG', 'trace');
            }
        }
    }
}
