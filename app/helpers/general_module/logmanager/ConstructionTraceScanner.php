<?php
// File: /app/helpers/general_module/logmanager/ConstructionTraceScanner.php

namespace App\Helpers\general_module\logmanager;

use App\Core\DB;
use App\Core\TraceManager;
use PDO;

class ConstructionTraceScanner
{
    public static function analyze(): array
    {
        return [
            'traceMode'          => self::isTraceModeActive(),
            'declaredTraceFiles' => self::findDeclaredFiles(),
            'traceSessions'      => self::fetchActiveTraceSessions(),
        ];
    }

    private static function isTraceModeActive(): bool
    {
        if (!empty($_SESSION['trace_mode'])) return true;

        $config = require CONFIG_PATH . '/app.php';
        return !empty($config['trace_mode']);
    }

    private static function fetchActiveTraceSessions(): array
    {
        try {
            $pdo = DB::connect();
            $stmt = $pdo->query("SELECT * FROM trace_sessions WHERE is_closed = 0 ORDER BY started_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            \App\Core\Logger::trigger(
                'Error in fetchActiveTraceSessions',
                \App\Helpers\general_module\logmanager\LogContextBuilder::enrich([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]),
                'ERROR',
                'system'
            );
            return [];
        }
    }

    private static function findDeclaredFiles(): array
    {
        $matches = [];
        $basePath = APP_PATH;
        $excludeDirs = ['vendor', 'core', 'helpers'];

        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basePath));
        foreach ($rii as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') continue;

            $filePath = $file->getRealPath();
            $relative = str_replace($basePath, '', $filePath);

            foreach ($excludeDirs as $skip) {
                if (strpos($relative, DIRECTORY_SEPARATOR . $skip . DIRECTORY_SEPARATOR) !== false) {
                    continue 2;
                }
            }

            $contents = @file_get_contents($filePath);
            if (!$contents) continue;

            if (preg_match('/Logger::startConstructionTrace\(\s*__FILE__\s*,\s*[\'"](.+?)[\'"]\s*\)/', $contents, $m)) {
                $matches[] = [
                    'file' => $filePath,
                    'note' => $m[1],
                ];
            } elseif (str_contains($contents, 'Logger::startConstructionTrace')) {
                $matches[] = [
                    'file' => $filePath,
                    'note' => null,
                ];
            }
        }

        return $matches;
    }

    public static function autoLogPageLoad($file, $context = [], string $tone = 'casual', string $verbosity = 'short'): void
    {
        $realFile = realpath($file);
        $sessionFile = $_SESSION['trace_file'] ?? null;

        error_log("TRACE DEBUG: autoLogPageLoad called. sessionFile=$sessionFile, realFile=$realFile");

        // 🔍 Detect mismatch — file is no longer marked for tracing
        if (!$sessionFile || $realFile !== $sessionFile) {
            error_log("TRACE AUTO: Trace ended due to file mismatch or missing trace line.");
            TraceManager::endTrace();
            return;
        }

        // ✅ Matched file: continue logging
        $context = array_merge([
            'file' => $file,
            'action' => 'auto page load'
        ], $context);

        \App\Core\Logger::trigger(
            'Page loaded',
            $context,
            'DEBUG',
            'trace',
            $tone,
            $verbosity
        );
    }
}
