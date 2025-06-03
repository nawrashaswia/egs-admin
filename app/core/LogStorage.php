<?php

namespace App\Core;

use App\Core\DB;
use App\Core\TraceManager;

class LogStorage
{
    /**
     * Store a general log in the `logs` table.
     */
    public static function storeLog(array $entry): void
    {
            error_log("[LOGSTORE] Storing to logs table: " . json_encode($entry));

        try {
            DB::connect()->insert('logs', [
                'trace_id'  => $entry['trace_id'] ?? null,
                'event'     => $entry['event'],
                'level'     => $entry['level'],
                'user'      => $entry['user'],
                'mode'      => $entry['mode'],
                'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
                'timestamp' => $entry['timestamp'],
                'context'   => is_string($entry['context']) 
                                ? $entry['context'] 
                                : json_encode($entry['context'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        } catch (\Throwable $e) {
            // 🛑 Fallback logging in case DB fails
            self::fallbackToFile($entry, '[DB failed: ' . $e->getMessage() . ']');
        }
    }

    /**
     * Store a construction-specific log in a JSON file.
     */
    public static function storeConstructionLog(array $entry): void
    {
        try {
            $path = TraceManager::getTraceLogPath($entry['trace_id'] ?? null);
            $dir = dirname($path);

            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $logs = [];
            if (file_exists($path)) {
                $logs = json_decode(file_get_contents($path), true);
                if (!is_array($logs)) $logs = [];
            }

            $logs[] = [
                'timestamp' => $entry['timestamp'],
                'event'     => $entry['event'],
                'level'     => $entry['level'],
                'user'      => $entry['user'],
                'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
                'context'   => is_string($entry['context']) 
                                ? $entry['context'] 
                                : json_encode($entry['context'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];

            file_put_contents($path, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } catch (\Throwable $e) {
            // 🛑 Fallback if JSON write fails
            self::fallbackToFile($entry, '[JSON write failed: ' . $e->getMessage() . ']');
        }
    }

    /**
     * Emergency fallback to flat file storage in critical cases.
     */
    private static function fallbackToFile(array $entry, string $note = ''): void
    {
        $entry['note'] = $note;
        $fallbackPath = LOGS_PATH . '/emergency_log.json';

        $line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        file_put_contents($fallbackPath, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
