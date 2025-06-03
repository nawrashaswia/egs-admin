<?php

namespace App\Core;

use App\Helpers\Core\ErrorHandler;
use App\Core\TraceManager;
use App\Core\Logger;

class Router
{
    private static array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public static function get(string $uri, callable|array|string $handler): void
    {
        self::$routes['GET'][self::normalize($uri)] = $handler;
    }

    public static function post(string $uri, callable|array|string $handler): void
    {
        error_log("[Router] Register POST: " . self::normalize($uri) . " => " . (is_string($handler) ? $handler : (is_callable($handler) ? 'callable' : 'other')));
        self::$routes['POST'][self::normalize($uri)] = $handler;
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = self::normalize(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        error_log("[Router] Dispatch: $method $uri");
        error_log("[Router] Registered POST routes: " . implode(', ', array_keys(self::$routes['POST'])));

        // After (line 25): URI normalization
        if (TraceManager::isTracing()) {
            Logger::trigger("Routing dispatch", ['method' => $method, 'uri' => $uri], 'DEBUG', 'trace');
        }

        // ✅ Direct route match
        if (isset(self::$routes[$method][$uri])) {
            self::resolve(self::$routes[$method][$uri], []);
            return;
        }

        // ✅ Dynamic route matching
        foreach (self::$routes[$method] as $route => $handler) {
            $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $route);
            $pattern = "#^" . rtrim($pattern, '/') . "/?$#";

            if (preg_match($pattern, $uri, $params)) {
                array_shift($params);
                self::resolve($handler, $params);
                return;
            }
        }

        // ❌ 404 - No route matched
        ErrorHandler::notFound('No route matched the requested URI.', $uri, [
            'method' => $method,
            'routes_loaded' => array_keys(self::$routes[$method]),
            'file' => __FILE__,
            'line' => __LINE__,
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ]);
    }

    private static function resolve($handler, array $params = []): void
    {
        error_log("[Router] Resolving handler: " . (is_string($handler) ? $handler : (is_callable($handler) ? 'callable' : 'other')));
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            exit;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            if (class_exists($class) && method_exists($class, $method)) {
                call_user_func_array([new $class, $method], $params);
                return;
            }

            ErrorHandler::notFound("Handler method not found: $class::$method", '', [
                'handler' => $handler,
                'params' => $params
            ]);
        }

        if (is_string($handler) && file_exists($handler)) {
            require $handler;
            return;
        }

        // ❌ Fallback: Invalid handler
        ErrorHandler::notFound('Invalid route handler encountered.', '', [
            'handler' => $handler,
            'params' => $params
        ]);
    }

    private static function normalize(string $path): string
    {
        return '/' . trim(str_replace(BASE_URL, '', $path), '/');
    }
}
