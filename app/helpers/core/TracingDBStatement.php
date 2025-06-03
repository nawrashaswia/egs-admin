<?php

namespace App\Helpers\Core;

use PDOStatement;
use App\Core\Logger;
use App\Core\TraceManager;

class TracingDBStatement extends PDOStatement
{
    protected function __construct() {} // required by PDO magic

    public function execute($params = null): bool
    {
        $sql = $this->queryString ?? '';
        $params = $params ?? [];

        // 😘 Skip logging self-logging inserts (to avoid infinite loops)
        if (
            stripos($sql, 'insert into logs') !== false ||
            stripos($sql, 'insert into construction_logs') !== false
        ) {
            return parent::execute($params);
        }

        // 💫 Start timer
        $start = microtime(true);

        try {
            $result = parent::execute($params);
        } catch (\PDOException $e) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
            $caller = $trace[2] ?? [];
            $file = $caller['file'] ?? 'unknown';
            $line = $caller['line'] ?? '??';

            // 😵‍💫 Log the error like a breakup text
            Logger::trigger("🔥 SQL Execution Failed", [
                'query' => $this->queryString,
                'params' => $params,
                'file' => $file,
                'line' => $line,
                'error' => $e->getMessage(),
                'tag' => 'db',
                'type' => 'failure'
            ], 'ERROR', TraceManager::isTracing() ? 'trace' : 'system', 'dev', 'short');

            throw $e; // 😢 still throw, but with love
        }

        // 🕰️ Measure duration
        $duration = round((microtime(true) - $start) * 1000, 2); // in ms
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        $caller = $trace[2] ?? [];
        $file = $caller['file'] ?? 'unknown';
        $line = $caller['line'] ?? '??';

        // 🧠 Identify query type
        $queryType = strtoupper(strtok(trim($sql), " ")); // SELECT, INSERT, etc.

        // 🚦 Set mood
        $level = $duration > 2000 ? 'WARN' : 'DEBUG';

        // 🧽 Filter out noisy, tiny queries (OPTIONAL)
        if (strlen($sql) < 20 && $level === 'DEBUG') {
            return $result;
        }

        // 😍 Log the juicy stuff
        Logger::trigger("📊 SQL Query Executed", [
            'query' => mb_strimwidth($sql, 0, 300, '...'),
            'params' => $params,
            'duration_ms' => $duration,
            'file' => $file,
            'line' => $line,
            'type' => $queryType,
            'tag' => 'db',
            'mode' => TraceManager::isTracing() ? 'trace' : 'system'
        ], $level, TraceManager::isTracing() ? 'trace' : 'system', 'dev', 'short');

        return $result;
    }
}
