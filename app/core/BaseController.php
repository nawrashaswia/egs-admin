<?php

namespace App\Core;

use App\Core\App;
use App\Core\ViewRenderer;

class BaseController
{
    protected function render(string $view, array $data = []): void
    {
        ViewRenderer::render($view, $data);
    }

    protected function partial(string $view, array $data = []): void
    {
        ViewRenderer::partial($view, $data);
    }

    protected function redirect(string $path): void
    {
        header("Location: " . BASE_URL . $path);
        exit;
    }

    protected function service(string $key): mixed
    {
        return App::get($key);
    }

    protected function user(): mixed
    {
        return App::get('auth')?->user();
    }

    /**
     * Shortcut to log a general or audit event
     */
    protected function log(string $event, array $context = [], string $level = 'INFO', string $mode = 'system'): void
    {
        \App\Core\Logger::trigger($event, $context, $level, $mode);
    }

    /**
     * Shortcut to log into trace logs if active
     */
    protected function trace(string $event, array $context = [], string $level = 'DEBUG'): void
    {
        if (\App\Core\TraceManager::isTracing()) {
            \App\Core\Logger::trigger($event, $context, $level, 'trace');
        }
    }
}
