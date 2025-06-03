<?php

namespace App\Core;

use App\Helpers\Core\ErrorHandler;
use App\Core\App;
use App\Core\TraceManager;
use App\Core\Logger;

class ViewRenderer
{
    public static function render(string $viewPath, array $data = []): void
    {
        extract(App::get('view.context', []));
        extract($data);

        $resolvedPath = self::resolveViewPath($viewPath);

        // After resolving viewPath
        if (TraceManager::isTracing()) {
            Logger::trigger("View rendered", ['path' => $viewPath, 'resolved' => $resolvedPath], 'DEBUG', 'trace');
        }

        if (!file_exists($resolvedPath)) {
            ErrorHandler::notFound(
                "View not found: $viewPath",
                $_SERVER['REQUEST_URI'] ?? '',
                [
                    'resolved_path' => $resolvedPath,
                    'view_requested' => $viewPath,
                    'source' => 'ViewRenderer::render',
                    'file' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? 'unknown',
                    'line' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['line'] ?? 'n/a',
                ]
            );
        }

        $layout = $data['layout'] ?? 'main';
        $useLayout = $layout !== false && $layout !== 'none';

        if (!$useLayout) {
            // Inject meta trace tag
            if (TraceManager::isTracing()) {
                echo '<meta name="trace-id" content="' . TraceManager::getTraceId() . '">';
            }
            require $resolvedPath;
            return;
        }

        ob_start();
        require $resolvedPath;
        $content = ob_get_clean();

        // Inject meta trace tag
        if (TraceManager::isTracing()) {
            echo '<meta name="trace-id" content="' . TraceManager::getTraceId() . '">';
        }

        $layoutPath = VIEWS_PATH . '/layout/' . rtrim($layout, '.php') . '.php';

        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            ErrorHandler::notFound(
                "Layout not found: $layout",
                $_SERVER['REQUEST_URI'] ?? '',
                [
                    'layout_requested' => $layout,
                    'resolved_path' => $layoutPath,
                    'source' => 'ViewRenderer::render',
                    'file' => __FILE__,
                    'line' => __LINE__,
                ]
            );
        }
    }

    public static function partial(string $viewPath, array $data = []): void
    {
        extract(App::get('view.context', []));
        extract($data);

        $resolvedPath = self::resolveViewPath($viewPath);

        // After resolving partial viewPath
        if (TraceManager::isTracing()) {
            Logger::trigger("Partial view rendered", ['path' => $viewPath, 'resolved' => $resolvedPath], 'DEBUG', 'trace');
        }

        if (file_exists($resolvedPath)) {
            require $resolvedPath;
        } else {
            ErrorHandler::notFound(
                "Partial view not found: $viewPath",
                $_SERVER['REQUEST_URI'] ?? '',
                [
                    'resolved_path' => $resolvedPath,
                    'view_requested' => $viewPath,
                    'source' => 'ViewRenderer::partial',
                    'file' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? 'unknown',
                    'line' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['line'] ?? 'n/a',
                ]
            );
        }
    }

    private static function resolveViewPath(string $viewPath): string
    {
        $viewPath = trim($viewPath, '/');

        if (str_starts_with($viewPath, 'modules/')) {
            return APP_PATH . '/' . $viewPath . '.php';
        }

        return VIEWS_PATH . '/' . $viewPath . '.php';
    }
}
