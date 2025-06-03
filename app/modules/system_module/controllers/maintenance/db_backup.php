<?php

use App\Core\DB;
use App\Core\Response;
use App\Helpers\Core\FlashHelper;

try {
    // 🔌 Connect to database and get DB name from config
    $pdo    = DB::connect();
    $config = require CONFIG_PATH . '/database.php';
    $dbName = $config['name'];

    // 📁 Prepare backup folder and file path
    $today      = date('Y-m-d');
    $timestamp  = date('Y-m-d_His');
    $backupDir  = BASE_PATH . "/storage/backups/$today";
    $fileName   = "full_backup_$timestamp.sql";
    $filePath   = "$backupDir/$fileName";
    $publicPath = BASE_URL . "/storage/backups/$today/$fileName";

    if (!is_dir($backupDir) && !@mkdir($backupDir, 0775, true)) {
        throw new Exception("Failed to create backup directory: $backupDir");
    }

    // 🧠 Start SQL dump
    $dump = "-- ✅ EG-ADMIN SQL BACKUP\n";
    $dump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    $dump .= "DROP DATABASE IF EXISTS `$dbName`;\n";
    $dump .= "CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;\n";
    $dump .= "USE `$dbName`;\n\n";

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $totalInserts = 0;

    foreach ($tables as $table) {
        // 📦 Structure
        $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        $dump .= "--\n-- Table structure for `$table`\n--\n\n";
        $dump .= "DROP TABLE IF EXISTS `$table`;\n";
        $dump .= $create['Create Table'] . ";\n\n";

        // 📊 Data
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            $dump .= "--\n-- Data for `$table`\n--\n\n";
            $cols = '`' . implode('`, `', array_keys($rows[0])) . '`';
            $batch = [];

            foreach ($rows as $i => $row) {
                $vals = array_map(fn($v) => $v === '' || $v === null ? 'NULL' : $pdo->quote($v), array_values($row));
                $batch[] = '(' . implode(',', $vals) . ')';
                $totalInserts++;

                if (count($batch) === 100 || $i === count($rows) - 1) {
                    $dump .= "INSERT INTO `$table` ($cols) VALUES\n" . implode(",\n", $batch) . ";\n\n";
                    $batch = [];
                }
            }
        }
    }

    // 💾 Save to disk
    file_put_contents($filePath, $dump);

    // ✅ Success summary
    $sizeBytes  = filesize($filePath);
    $sizeKB     = round($sizeBytes / 1024);
    $sizeMB     = round($sizeKB / 1024, 2);
    $tableCount = count($tables);
    $timeNow    = date('Y-m-d H:i:s');

            $_SESSION['db_summary'] = <<<TEXT
        ✔️ Action: Full Backup
        📦 File: $fileName
        📅 Time: $timeNow
        📁 Folder: /storage/backups/
        📊 Tables: $tableCount
        📈 Rows Inserted: $totalInserts
        🗜 Size: $sizeMB MB ($sizeKB KB)
        🔗 [Download SQL File]($publicPath)
        TEXT;

    FlashHelper::set('success', ' Full database backup saved as <code>' . $fileName . '</code>.');
    header('Location: ' . BASE_URL . '/system/maintenance');
    exit;


} catch (Throwable $e) {
    if (DEBUG_MODE) {
        echo "<pre style='color:red'>" . htmlspecialchars($e) . "</pre>";
        exit;
    }
FlashHelper::set('error', ' Backup failed: ' . $e->getMessage());
header('Location: ' . BASE_URL . '/system/maintenance');
exit;


}
