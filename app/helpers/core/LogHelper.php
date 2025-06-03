<?php

namespace App\Helpers\Core;

/**
 * Simple file-based logger for errors, warnings, and info messages
 */
class LogHelper
{
    /**
     * Log an error-level message
     *
     * @param string $message
     * @param array $context
     */
    public static function error(string $message, array $context = []): void
    {
        self::write('error', $message, $context);
    }

    /**
     * Log a warning-level message
     *
     * @param string $message
     * @param array $context
     */
    public static function warning(string $message, array $context = []): void
    {
        self::write('warning', $message, $context);
    }

    /**
     * Log an info-level message
     *
     * @param string $message
     * @param array $context
     */
    public static function info(string $message, array $context = []): void
    {
        self::write('info', $message, $context);
    }

    /**
     * Write a log entry to the log file
     *
     * @param string $level
     * @param string $message
     * @param array $context
     */
    private static function write(string $level, string $message, array $context = []): void
    {
        $date = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        $logLine = "[$date][$level] $message $contextStr\n";

        $logFile = defined('LOGS_PATH') ? LOGS_PATH . '/app.log' : __DIR__ . '/../../../../storage/logs/app.log';

        try {
            file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // You can choose to silently fail or handle logging errors elsewhere
        }
    }
}
