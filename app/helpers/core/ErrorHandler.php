<?php
namespace App\Helpers\Core;

use Throwable;
use App\Core\Logger;

class ErrorHandler
{
    public static function init()
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function notFound(string $reason, string $uri = '', array $debug = [])
    {
        http_response_code(404);

        $diagnosis = self::diagnoseProblem('404', $reason, $debug);
        self::render('404', [
            'reason' => $reason,
            'code' => '404',
            'uri' => $uri,
            'debug' => $debug,
            'diagnosis' => $diagnosis,
        ]);
    }

    public static function serverError(string $reason, string $code = 'SRV1', array $debug = [])
    {
        http_response_code(500);

        $diagnosis = self::diagnoseProblem($code, $reason, $debug);
        self::render('general', compact('reason', 'code', 'debug', 'diagnosis'));
    }

    public static function jsonError(string $reason, string $code = 'JSON_ERR', array $debug = [])
    {
        http_response_code(500);

        echo json_encode([
            'error' => $reason,
            'code' => $code,
            'diagnosis' => self::diagnoseProblem($code, $reason, $debug),
            'debug' => defined('APP_DEBUG') && APP_DEBUG ? $debug : null
        ]);
        exit;
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) return;

        $nonFatal = [
            E_NOTICE, E_USER_NOTICE,
            E_WARNING, E_USER_WARNING,
            E_DEPRECATED, E_USER_DEPRECATED,
        ];

        $context = [
            'file' => $errfile,
            'line' => $errline,
            'errno' => $errno,
            'action' => 'execute PHP code',
        ];

        $level = in_array($errno, $nonFatal) ? 'WARNING' : 'ERROR';
        $mode = config('trace_mode', false) ? 'trace' : 'system';

        // Log as a story, always to logs, and to construction_logs if trace mode
        try {
            Logger::trigger(
                "PHP Error: $errstr",
                $context,
                $level,
                $mode
            );
        } catch (\Throwable $e) {
            self::log("[NativeTalker] Failed to log error: $errstr", $context);
        }

        if (in_array($errno, $nonFatal)) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                self::log("Non-fatal error: $errstr", $context);
            }
            return;
        }

        self::serverError("[$errno] $errstr", 'PHP_ERR', $context);
    }

    public static function handleException(Throwable $e)
    {
        $context = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'action' => 'execute application code',
        ];
        $mode = config('trace_mode', false) ? 'trace' : 'system';
        // Log as a story, always to logs, and to construction_logs if trace mode
        try {
            Logger::trigger(
                "Uncaught Exception: " . $e->getMessage(),
                $context,
                'ERROR',
                $mode
            );
        } catch (\Throwable $ex) {
            self::log("[NativeTalker] Failed to log exception: " . $e->getMessage(), $context);
        }
        self::serverError($e->getMessage(), 'EXC', $context);
    }

    public static function handleShutdown()
    {
        $error = error_get_last();

        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $context = [
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $error['type'],
                'message' => $error['message'],
                'action' => 'shutdown',
            ];
            $mode = config('trace_mode', false) ? 'trace' : 'system';
            // Log as a story, always to logs, and to construction_logs if trace mode
            try {
                Logger::trigger(
                    "Shutdown Error: {$error['message']}",
                    $context,
                    'ERROR',
                    $mode
                );
            } catch (\Throwable $ex) {
                self::log("[NativeTalker] Failed to log shutdown error: {$error['message']}", $context);
            }
            self::serverError("Shutdown Error: {$error['message']}", 'FATAL', $context);
        }
    }

    private static function render(string $view, array $data)
    {
        extract($data);
        $viewPath = APP_PATH . '/views/error/' . $view . '.php';

        if (defined('IS_AJAX') && IS_AJAX) {
            self::jsonError($reason, $code, $debug ?? []);
        }

        if (defined('APP_DEBUG') && APP_DEBUG) {
            self::log("[$code] $reason", $data);
        }

        if (file_exists($viewPath)) {
            include $viewPath;
            exit;
        }

        echo "<h1>Error $code</h1><p>$reason</p>";
        if (!empty($diagnosis)) {
            echo "<h3>Diagnosis</h3><pre>" . print_r($diagnosis, true) . "</pre>";
        }
        exit;
    }

    private static function log(string $message, array $context = [])
    {
        $logDir = __DIR__ . '/../../../logs/';
        $logFile = $logDir . 'error_debug.log';

        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logEntry = date('Y-m-d H:i:s') . " | $message\n" . print_r($context, true) . "\n\n";
        error_log($logEntry, 3, $logFile);
    }

    private static function diagnoseProblem(string $code, string $message, array $context): array
    {
        $origin = $context['file'] ?? $context['origin_file'] ?? 'unknown';
        $line = $context['line'] ?? 'n/a';
        $trace = $context['trace'] ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $memory = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);

        $source = str_contains($origin, '/views/') ? 'View Layer' :
                  (str_contains($origin, '/controllers/') ? 'Controller' :
                  (str_contains($origin, '/routes.map') ? 'Routing' :
                  (str_contains($origin, '/core/') ? 'Kernel/Core' :
                  (str_contains($origin, 'database') ? 'Database' :
                  'Unknown'))));

        $suggestion = match ($source) {
            'View Layer'   => 'Check if the view file exists and is correctly named.',
            'Controller'   => 'Verify that the controller class and method are properly defined.',
            'Routing'      => 'Inspect your routes.map.php file for missing or misnamed routes.',
            'Kernel/Core'  => 'Check AppKernel boot, middleware, or autoload logic.',
            'Database'     => 'Check DB connection, queries, or model loading issues.',
            default        => 'Inspect file path, error trace, or logs for deeper insight.'
        };

        return [
            'symptoms'   => $message,
            'source'     => $source,
            'file'       => $origin,
            'line'       => $line,
            'suggestion' => $suggestion,
            'code'       => $code,
            'memory'     => number_format($memory / 1024 / 1024, 2) . ' MB',
            'peak_memory'=> number_format($peak / 1024 / 1024, 2) . ' MB',
            'trace'      => defined('APP_DEBUG') && APP_DEBUG ? $trace : null,
        ];
    }
}
