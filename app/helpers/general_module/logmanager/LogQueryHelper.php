<?php
// File: /app/helpers/general_module/logmanager/LogQueryHelper.php

namespace App\Helpers\general_module\logmanager;

use App\Core\DB;
use PDO;

class LogQueryHelper
{
    public static function fetchGeneralLogs(int $limit = 50, array $filters = []): array
    {
        try {
            $pdo = DB::connect();
            $limit = (int)$limit;
            $where = [];
            $params = [];
            if (!empty($filters['level'])) {
                $where[] = 'level = ?';
                $params[] = $filters['level'];
            }
            if (!empty($filters['user'])) {
                $where[] = 'user LIKE ?';
                $params[] = '%' . $filters['user'] . '%';
            }
            if (!empty($filters['trace_id'])) {
                $where[] = 'trace_id LIKE ?';
                $params[] = '%' . $filters['trace_id'] . '%';
            }
            if (!empty($filters['tag'])) {
                $where[] = 'tag LIKE ?';
                $params[] = '%' . $filters['tag'] . '%';
            }
            $sql = 'SELECT * FROM logs';
            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY timestamp DESC LIMIT ' . $limit;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            \App\Core\Logger::trigger(
                'Error in fetchGeneralLogs',
                \App\Helpers\general_module\logmanager\LogContextBuilder::enrich([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]),
                'ERROR',
                'system'
            );
            return [self::errorStub('general logs', $e)];
        }
    }

    public static function fetchConstructionLogs(int $limit = 50): array
    {
        try {
            $pdo = DB::connect();
            $limit = (int)$limit;
            $stmt = $pdo->query("SELECT * FROM construction_logs ORDER BY timestamp DESC LIMIT $limit");
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($logs as &$log) {
                $log['mode'] = 'trace';
                $log['user'] = $log['user'] ?? 'system';
            }

            return $logs;
        } catch (\Throwable $e) {
            \App\Core\Logger::trigger(
                'Error in fetchConstructionLogs',
                \App\Helpers\general_module\logmanager\LogContextBuilder::enrich([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]),
                'ERROR',
                'system'
            );
            return [self::errorStub('construction logs', $e)];
        }
    }

    public static function fetchTraceSessions(): array
    {
        try {
            $pdo = DB::connect();
            $stmt = $pdo->query("SELECT * FROM trace_sessions WHERE is_closed = 0 ORDER BY started_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            \App\Core\Logger::trigger(
                'Error in fetchTraceSessions',
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

    public static function findDeclaredTraceFiles(): array
    {
        $basePath     = APP_PATH;
        $matches      = [];
        $excludeDirs  = ['helpers', 'core', 'vendor'];

        try {
            $pdo = DB::connect();
            $existing = $pdo->query("SELECT DISTINCT file FROM trace_sessions")->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
            \App\Core\Logger::trigger(
                'Error in findDeclaredTraceFiles',
                \App\Helpers\general_module\logmanager\LogContextBuilder::enrich([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]),
                'ERROR',
                'system'
            );
            $existing = [];
        }

        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basePath));
        foreach ($rii as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') continue;

            $path = $file->getRealPath();

            // Skip if file was already registered in DB
            if (in_array($path, $existing)) continue;

            $relative = str_replace($basePath, '', $path);
            foreach ($excludeDirs as $dir) {
                if (strpos($relative, DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR) !== false) {
                    continue 2;
                }
            }

            $contents = @file_get_contents($path);
            if (str_contains($contents, 'Logger::startConstructionTrace(')) {
                $matches[] = [
                    'file' => $path,
                    'note' => self::extractNote($contents)
                ];
            }
        }

        return $matches;
    }

    private static function extractNote(string $content): ?string
    {
        if (preg_match('/Logger::startConstructionTrace\(\s*__FILE__\s*,\s*[\'"](.+?)[\'"]\s*\)/', $content, $m)) {
            return $m[1];
        }
        return null;
    }

    private static function errorStub(string $source, \Throwable $e): array
    {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'event'     => "⚠️ Failed to fetch $source",
            'level'     => 'ERROR',
            'user'      => 'system',
            'mode'      => 'system',
            'trace_id'  => 'N/A',
            'context'   => json_encode(['error' => $e->getMessage()])
        ];
    }
}
