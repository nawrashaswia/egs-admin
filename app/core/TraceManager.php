<?php

namespace App\Core;

use App\Core\DB;

class TraceManager
{
    private static ?string $traceId = null;

    /**
     * Checks if trace mode is globally enabled AND a trace session is active.
     */
    public static function isTracing(): bool
    {
        return config('trace_mode', false) && self::getTraceId() !== null;
    }

    /**
     * Returns the current trace ID from memory or session.
     * Performs self-healing checks:
     * - Ends trace if trace_mode disabled
     * - Ends trace if file was removed
     * - Ends trace if Logger line removed
     */
    public static function getTraceId(): ?string
    {
        // 💣 Kill if global trace mode is off
        if (!config('trace_mode')) {
            self::endTrace();
            return null;
        }

        // Return cached ID
        if (self::$traceId !== null) return self::$traceId;

        // Nothing in session = no trace
        if (empty($_SESSION['trace_id']) || empty($_SESSION['trace_file'])) {
            return null;
        }

        $fileReal = realpath($_SESSION['trace_file']);

        // 💣 Kill if file is missing or unreadable
        if (!$fileReal || !file_exists($fileReal)) {
            self::endTrace();
            return null;
        }

        // 💣 Kill if Logger::startConstructionTrace not found anymore
        $contents = @file_get_contents($fileReal);
        if (!$contents || !str_contains($contents, 'Logger::startConstructionTrace')) {
            self::endTrace();
            return null;
        }

        // ✅ Good session
        self::$traceId = $_SESSION['trace_id'];
        return self::$traceId;
    }

    /**
     * Starts a new trace session for a file.
     */
    public static function startTrace(string $file, string $notes = ''): void
    {
        if (!config('trace_mode')) return;

        $fileReal = realpath($file);

        // ✅ Avoid duplicate for same file
        if (self::getTraceId() && ($_SESSION['trace_file'] ?? null) === $fileReal) {
            return;
        }

        // 🔁 End previous if switching file
        if (isset($_SESSION['trace_file']) && $_SESSION['trace_file'] !== $fileReal) {
            self::endTrace();
        }

        // 🧹 Close any existing sessions in DB for this file
        DB::query("UPDATE trace_sessions SET is_closed = 1 WHERE file = ? AND is_closed = 0", [$fileReal]);

        $traceId = 'TRACE-' . date('Ymd') . '-' . substr(md5($fileReal . microtime()), 0, 8);
        $_SESSION['trace_id'] = $traceId;
        $_SESSION['trace_file'] = $fileReal;
        self::$traceId = $traceId;

        error_log("TRACE DEBUG: startTrace called. Setting session trace_file=$fileReal");

        DB::insert('trace_sessions', [
            'trace_id'   => $traceId,
            'started_at' => date('Y-m-d H:i:s'),
            'started_by' => $_SESSION['user_name'] ?? 'system',
            'file'       => $fileReal,
            'notes'      => $notes,
            'is_closed'  => 0,
        ]);

        self::initializeJsonLog($traceId);
    }

    /**
     * Ends the current trace session if active.
     */
    public static function endTrace(): void
    {
        $traceId = $_SESSION['trace_id'] ?? null;
        if (!$traceId) return;

        DB::update('trace_sessions', ['is_closed' => 1], ['trace_id' => $traceId]);

        unset($_SESSION['trace_id'], $_SESSION['trace_file']);
        self::$traceId = null;
    }

    /**
     * Returns true if a session is active.
     */
    public static function isTraceSessionActive(): bool
    {
        return self::getTraceId() !== null;
    }

    /**
     * Returns the file path for the trace's JSON mirror.
     */
    public static function getTraceLogPath(?string $traceId = null): string
    {
        $traceId = $traceId ?? self::getTraceId();
        return LOGS_PATH . '/trace/' . $traceId . '.json';
    }

    /**
     * Ensures trace JSON file is initialized.
     */
    private static function initializeJsonLog(string $traceId): void
    {
        $logDir = LOGS_PATH . '/trace';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        $logFile = $logDir . '/' . $traceId . '.json';
        if (!file_exists($logFile)) {
            file_put_contents($logFile, "[]");
        }
    }
}
