<?php

namespace App\Helpers\Core;

use App\Core\ViewRenderer;
use App\Core\Router;
use App\Core\app;
class RouteHelper
{
    public static function get(string $path, $handler): void
    {
        Router::get($path, $handler);
    }

    public static function post(string $path, $handler): void
    {
        Router::post($path, $handler);
    }

public static function view(string $path, string $viewFile, array $context = []): void
{
    self::get($path, function () use ($viewFile, $context) {
        $resolvedPath = null;

        // 1. Try regular view inside VIEWS_PATH
        $attempt1 = VIEWS_PATH . '/' . $viewFile . '.php';
        if (file_exists($attempt1)) {
            $resolvedPath = $viewFile;
        }

        // 2. Try full path like modules/... from APP_PATH
        $attempt2 = APP_PATH . '/' . $viewFile . '.php';
        if (!$resolvedPath && file_exists($attempt2)) {
            // Convert to relative format from APP_PATH for ViewRenderer
            $resolvedPath = str_replace([APP_PATH . '/', '.php'], '', $attempt2);
        }

        if (!$resolvedPath) {
            App::set('route_error', "View not found: $viewFile");
            require VIEWS_PATH . '/error/router-error.php';
            return;
        }

        ViewRenderer::render($resolvedPath, $context);
    });
}

}
