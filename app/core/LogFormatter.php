<?php

namespace App\Core;

use App\Core\TraceManager;

class LogFormatter
{
    /**
     * Format a log entry in a conversational + compact manner.
     */
    public static function formatConversational(
        string $event,
        array $context = [],
        string $level = 'INFO',
        string $mode = 'system',
        string $tone = 'casual',
        string $verbosity = 'verbose'
    ): array {
        $user = $_SESSION['user_name'] ?? ($_SESSION['admin_user'] ?? 'system');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $when = date('Y-m-d H:i:s');
        $traceId = TraceManager::getTraceId();

        // 🔍 Enrich context
        $context['method'] = $context['method'] ?? ($_SERVER['REQUEST_METHOD'] ?? 'CLI');
        $context['url'] = $context['url'] ?? ($_SERVER['REQUEST_URI'] ?? 'n/a');
        $context['module'] = $context['module'] ?? self::detectModule($context['file'] ?? null);
        if ($context['module'] === 'unknown') {
            // Use HiLoSequence's robust detection as a fallback
            try {
                if (class_exists('App\\Helpers\\Core\\HiLoSequence')) {
                    $context['module'] = \App\Helpers\Core\HiLoSequence::detectModuleName();
                }
            } catch (\Throwable $e) {
                // If detection fails, keep as 'unknown'
            }
        }

        // Ensure 'file' is set in context for clear logs
        if (empty($context['file']) || $context['file'] === 'unknown') {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
            foreach ($backtrace as $trace) {
                if (!empty($trace['file']) && !str_contains($trace['file'], 'Logger.php') && !str_contains($trace['file'], 'LogFormatter.php')) {
                    $context['file'] = $trace['file'];
                    break;
                }
            }
            // If still not found, fallback to this file
            if (empty($context['file'])) {
                $context['file'] = __FILE__;
            }
        }

        $file = $context['file'] ?? 'unknown';
        $line = $context['line'] ?? null;

        // 🎯 Slim, one-liner event
        $emoji = self::getEmojiForLevel($level);
        $shortFile = basename($file);
        $eventMsg = "{$emoji} {$event} @ {$shortFile}" . ($line ? ":{$line}" : "");

        // 🧼 Trim non-essential context
        unset($context['user'], $context['ip'], $context['level'], $context['event'], $context['mode']);

        return [
            'trace_id'  => $traceId,
            'event'     => $eventMsg,
            'level'     => strtoupper($level),
            'user'      => $user,
            'ip'        => $ip,
            'timestamp' => $when,
            'context'   => $context
        ];
    }

    /**
     * Guess module name from path.
     */
    private static function detectModule(?string $path): string
    {
        if (!$path) return 'unknown';
        if (preg_match('#modules/([^/]+)/#', $path, $matches)) {
            return $matches[1];
        }
        return 'unknown';
    }

    /**
     * Return emoji for log level.
     */
    private static function getEmojiForLevel(string $level): string
    {
        return match (strtoupper($level)) {
            'ERROR' => '🔥',
            'DEBUG' => '🔧',
            'INFO' => '📦',
            'WARN', 'WARNING' => '⚠️',
            'AUDIT' => '🧾',
            default => '🔍',
        };
    }
}
