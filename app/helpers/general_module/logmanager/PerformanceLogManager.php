<?php

namespace App\Helpers\General_Module\LogManager;

use App\Core\Logger;
use App\Core\TraceManager;

class PerformanceLogManager
{
    private static float $startTime;
    private static int $fileCountStart;

    public static function start(): void
    {
        self::$startTime = microtime(true);
        self::$fileCountStart = count(get_included_files());
    }

    public static function end(): void
    {
        $duration = round((microtime(true) - self::$startTime) * 1000, 2); // ms
        $memory   = memory_get_peak_usage(true); // bytes
        $loadedFiles = count(get_included_files());
        $fileDelta = $loadedFiles - self::$fileCountStart;

        $thresholds = [
            'duration_ms' => 3000,
            'memory_mb'   => 50,
            'file_count'  => 100,
        ];

        $isHeavy = (
            $duration > $thresholds['duration_ms'] ||
            ($memory / 1024 / 1024) > $thresholds['memory_mb'] ||
            $fileDelta > $thresholds['file_count']
        );

        $isNearHeavy = (
            $duration > $thresholds['duration_ms'] * 0.8 ||
            ($memory / 1024 / 1024) > $thresholds['memory_mb'] * 0.8 ||
            $fileDelta > $thresholds['file_count'] * 0.8
        );

        try {
            if ($isHeavy) {
                Logger::trigger('🚨 Heavy request detected',
                    LogContextBuilder::enrich([
                        'duration_ms' => $duration,
                        'memory_peak_mb' => round($memory / 1024 / 1024, 2),
                        'included_files' => $loadedFiles,
                        'new_files' => $fileDelta,
                        'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
                        'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
                        'user' => $_SESSION['user_name'] ?? 'guest',
                        'agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
                        'threshold' => 'heavy',
                    ]),
                    'WARN',
                    TraceManager::isTracing() ? 'trace' : 'system',
                    'dev',
                    'short'
                );
            } elseif ($isNearHeavy) {
                Logger::trigger('⚠️ Near-heavy request detected',
                    LogContextBuilder::enrich([
                        'duration_ms' => $duration,
                        'memory_peak_mb' => round($memory / 1024 / 1024, 2),
                        'included_files' => $loadedFiles,
                        'new_files' => $fileDelta,
                        'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
                        'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
                        'user' => $_SESSION['user_name'] ?? 'guest',
                        'agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
                        'threshold' => 'near-heavy',
                    ]),
                    'INFO',
                    TraceManager::isTracing() ? 'trace' : 'system',
                    'dev',
                    'short'
                );
            }
        } catch (\Throwable $e) {
            Logger::trigger('Error in PerformanceLogManager::end',
                LogContextBuilder::enrich([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]),
                'ERROR',
                'system'
            );
        }
    }
}
