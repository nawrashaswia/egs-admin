<?php

use App\Core\DB;
use App\Helpers\Core\FlashHelper;

try {
    $pdo    = DB::connect();
    $config = require CONFIG_PATH . '/database.php';
    $dbName = $config['name'];

    // ✅ Resolve today's backup directory
    $backupDir = STORAGE_PATH . '/backups/' . date('Y-m-d');
    $files     = glob($backupDir . '/full_backup_*.sql');

    if (!$files) {
        throw new Exception('❌ No backup file found for today.');
    }

    // ✅ Use most recent SQL backup
    rsort($files); // most recent first
    $latest = $files[0];

    if (!is_readable($latest)) {
        throw new Exception("❌ Backup file is not readable: $latest");
    }

    $sql = file_get_contents($latest);
    if (!$sql) {
        throw new Exception("❌ Backup file is empty or unreadable.");
    }

    // ✅ Perform restore
    $pdo->exec("SET foreign_key_checks = 0;");
    $pdo->exec($sql);
    $pdo->exec("SET foreign_key_checks = 1;");

    // ✅ Generate summary
    $tableCount  = substr_count($sql, 'CREATE TABLE');
    $insertCount = substr_count($sql, 'INSERT INTO');
    $timeNow     = date('Y-m-d H:i:s');

    $_SESSION['db_summary'] = <<<TEXT
✔️ Action: Database Restore
📂 Source File: <code>{$latest}</code>
📅 Time: {$timeNow}
📊 Tables Created: {$tableCount}
📈 Rows Inserted: {$insertCount}
TEXT;

    FlashHelper::set('success', '✅ Database restored successfully from <code>' . basename($latest) . '</code>.');
    header('Location: ' . BASE_URL . '/system/maintenance');
    exit;

} catch (Throwable $e) {
    if (DEBUG_MODE) {
        echo "<pre style='color:red'>" . htmlspecialchars($e) . "</pre>";
        exit;
    }

    FlashHelper::set('error', '❌ Restore failed: ' . $e->getMessage());
    header('Location: ' . BASE_URL . '/system/maintenance');
    exit;
}
