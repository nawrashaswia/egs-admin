<?php
// Standalone DB and log test script

// Load DB config
$config = require __DIR__ . '/../config/database.php';

// Manual test for normal log via Logger
require_once __DIR__ . '/../app/core/Logger.php';
require_once __DIR__ . '/../app/core/LogFormatter.php';
require_once __DIR__ . '/../app/core/TraceManager.php';
require_once __DIR__ . '/../app/core/DB.php';

use App\Core\Logger;

// Define LOGS_PATH for emergency log fallback
if (!defined('LOGS_PATH')) {
    define('LOGS_PATH', __DIR__ . '/../logs');
}

// Minimal config() for standalone test
if (!function_exists('config')) {
    function config($key, $default = null) {
        if ($key === 'trace_mode') return false; // For test, disable trace mode
        return $default;
    }
}

Logger::trigger('Manual test log from test_db_log.php', ['manual' => true], 'INFO', 'system');
echo "<b>Manual Logger::trigger() with 'system' mode called.</b><br>";

function test_db_connection($config) {
    $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['name']};charset={$config['charset']}";
    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        echo "<b>DB Connection:</b> <span style='color:green'>SUCCESS</span><br>";
        return $pdo;
    } catch (PDOException $e) {
        echo "<b>DB Connection:</b> <span style='color:red'>FAILED</span><br>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        return false;
    }
}

function test_log_insert($pdo, $table, $data) {
    $fields = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));
        echo "<b>Insert into $table:</b> <span style='color:green'>SUCCESS</span><br>";
    } catch (PDOException $e) {
        echo "<b>Insert into $table:</b> <span style='color:red'>FAILED</span><br>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    }
}

echo "<h2>DB & Log Table Test</h2>";
$pdo = test_db_connection($config);
if ($pdo) {
    // Test normal log
    test_log_insert($pdo, 'logs', [
        'trace_id' => null,
        'event' => 'Test normal log',
        'level' => 'INFO',
        'user' => 'testuser',
        'mode' => 'system',
        'ip' => '127.0.0.1',
        'timestamp' => date('Y-m-d H:i:s'),
        'context' => json_encode(['test' => 'normal']),
    ]);
    // Test construction log
    test_log_insert($pdo, 'construction_logs', [
        'trace_id' => 'TRACE-TEST',
        'event' => 'Test construction log',
        'level' => 'DEBUG',
        'user' => 'testuser',
        'mode' => 'trace',
        'ip' => '127.0.0.1',
        'timestamp' => date('Y-m-d H:i:s'),
        'context' => json_encode(['test' => 'construction']),
    ]);
    // Test audit log
    test_log_insert($pdo, 'logs', [
        'trace_id' => null,
        'event' => 'Test audit log',
        'level' => 'AUDIT',
        'user' => 'testuser',
        'mode' => 'audit',
        'ip' => '127.0.0.1',
        'timestamp' => date('Y-m-d H:i:s'),
        'context' => json_encode(['test' => 'audit']),
    ]);
}

echo "<hr><b>Done.</b>"; 