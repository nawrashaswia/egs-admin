<?php

namespace App\Core;

use PDO;
use PDOException;
use App\Core\TraceManager;
use App\Core\Logger;
use App\Helpers\Core\TracingDBPDO;

class DB
{
    private static ?PDO $pdo = null;

    /**
     * Connects to the database and returns a PDO instance.
     */
    public static function connect(): PDO
    {
        if (self::$pdo !== null) return self::$pdo;

        $config = $GLOBALS['config']['database'] ?? require CONFIG_PATH . '/database.php';
        $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['name']};charset={$config['charset']}";

        error_log('DEBUG: DB connect() using DSN: ' . $dsn . ' USER: ' . $config['username']);

        try {
            // 🔁 Use our Tracing wrapper instead of native PDO
            self::$pdo = new TracingDBPDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Quick connection test
            try {
                $test = self::$pdo->query('SELECT 1')->fetchColumn();
                error_log('DEBUG: DB test SELECT 1 result: ' . var_export($test, true));
            } catch (PDOException $e) {
                error_log('DEBUG: DB test SELECT 1 failed: ' . $e->getMessage());
            }

            return self::$pdo;
        } catch (PDOException $e) {
            error_log('DEBUG: DB connection failed: ' . $e->getMessage());

            Logger::trigger("❌ DB connection failed", [
                'dsn' => $dsn,
                'message' => $e->getMessage()
            ], 'ERROR');

            throw $e;
        }
    }

    /**
     * Execute an SQL query with optional parameters and return result or success.
     */
    public static function query(string $sql, array $params = []): array|bool
    {
        $start = microtime(true);
        $mode = TraceManager::isTracing() ? 'trace' : 'system';

        try {
            $stmt = self::connect()->prepare($sql);
            $stmt->execute($params);
            $duration = round((microtime(true) - $start) * 1000, 2);

            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
            $caller = $backtrace[1] ?? [];
            $originFile = $caller['file'] ?? 'unknown';
            $originLine = $caller['line'] ?? null;

            $level = $duration > 2000 ? 'WARN' : 'DEBUG';

            Logger::trigger("📥 SQL Query Executed", [
                'sql' => $sql,
                'params' => $params,
                'duration_ms' => $duration,
                'file' => $originFile,
                'line' => $originLine
            ], $level, $mode, 'dev', 'short');

            if (str_starts_with(trim(strtolower($sql)), 'select')) {
                return $stmt->fetchAll();
            }

            return true;
        } catch (PDOException $e) {
            Logger::trigger("⛔ SQL Query Failed", [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage(),
                'file' => $originFile ?? 'unknown'
            ], 'ERROR', $mode, 'dev', 'verbose');

            throw $e;
        }
    }

    /**
     * Perform an INSERT operation.
     */
    public static function insert(string $table, array $data): bool
    {
        $fields = implode(', ', array_map(fn($f) => "`$f`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $values = array_values($data);
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";

        return self::connect()->prepare($sql)->execute($values);
    }

    /**
     * Perform an UPDATE operation.
     */
    public static function update(string $table, array $data, array $where): bool
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
        $whereClause = implode(' AND ', array_map(fn($k) => "$k = ?", array_keys($where)));
        $sql = "UPDATE {$table} SET {$set} WHERE {$whereClause}";
        $params = array_merge(array_values($data), array_values($where));

        return self::connect()->prepare($sql)->execute($params);
    }

    /**
     * Perform a DELETE operation.
     */
    public static function delete(string $table, array $where): bool
    {
        $whereClause = implode(' AND ', array_map(fn($k) => "$k = ?", array_keys($where)));
        $sql = "DELETE FROM {$table} WHERE {$whereClause}";

        return self::connect()->prepare($sql)->execute(array_values($where));
    }
}
