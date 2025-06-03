<?php

use App\Helpers\Core\FlashHelper;
use App\Core\DB;
try {
    $pdo    = DB::connect();
    $config = require CONFIG_PATH . '/database.php';
    $dbName = $config['name'];

    // ✅ Prepare paths
    $rootPath   = BASE_PATH; // Points to /www
    $today      = date('Y-m-d');
    $timestamp  = date('Y-m-d_His');
    $backupDir  = STORAGE_PATH . "/backups/$today";
    $sqlFile    = "$backupDir/full_backup_{$timestamp}.sql";
    $zipName    = "egs-admin-full_{$timestamp}.zip";
    $zipPath    = "$backupDir/$zipName";
    $publicLink = BASE_URL . "/storage/backups/$today/$zipName";

    if (!is_dir($backupDir) && !@mkdir($backupDir, 0775, true)) {
        throw new Exception("Unable to create backup directory: $backupDir");
    }

    // ✅ Dump SQL
    $sql = "-- EG-ADMIN FULL DB BACKUP\n-- Generated: $timestamp\n\n";
    $sql .= "DROP DATABASE IF EXISTS `$dbName`;\n";
    $sql .= "CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;\n";
    $sql .= "USE `$dbName`;\n\n";

    $tables    = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $rowCount  = 0;

    foreach ($tables as $table) {
        $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        $sql .= "-- Structure for `$table`\nDROP TABLE IF EXISTS `$table`;\n{$create['Create Table']};\n\n";

        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            $cols = '`' . implode('`, `', array_keys($rows[0])) . '`';
            $batch = [];

            foreach ($rows as $i => $row) {
                $vals = array_map(fn($v) => ($v === '' || $v === null) ? 'NULL' : $pdo->quote($v), array_values($row));
                $batch[] = '(' . implode(',', $vals) . ')';
                $rowCount++;

                if (count($batch) === 100 || $i === count($rows) - 1) {
                    $sql .= "INSERT INTO `$table` ($cols) VALUES\n" . implode(",\n", $batch) . ";\n\n";
                    $batch = [];
                }
            }
        }
    }

    file_put_contents($sqlFile, $sql);

    // ✅ Create ZIP (includes all of /www + SQL file)
    $zip = new ZipArchive();
    if (!$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        throw new Exception("Unable to create ZIP archive: $zipPath");
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        $localPath = 'www/' . substr($file->getRealPath(), strlen($rootPath) + 1);
        $file->isDir()
            ? $zip->addEmptyDir($localPath)
            : $zip->addFile($file->getRealPath(), $localPath);
    }

    $zip->addFile($sqlFile, basename($sqlFile));
    $zip->close();
    unlink($sqlFile);

    // ✅ Summary
    $sizeMB = round(filesize($zipPath) / 1024 / 1024, 2);
    $now    = date('Y-m-d H:i:s');

$tablesCount = count($tables);

$_SESSION['db_summary'] = <<<TEXT
✔️ Action: Full System Backup  
📅 Time: {$now}  
📁 Contents: Full /www folder + full DB dump  
📊 Tables: {$tablesCount}  
📈 Rows: {$rowCount}  
🗜 Size: {$sizeMB} MB  
🔗 [Download ZIP]({$publicLink})
TEXT;


    FlashHelper::set('success', 'Full system backup created: <code>' . $zipName . '</code>');
    header('Location: ' . BASE_URL . '/system/maintenance');
    exit;

} catch (Throwable $e) {
    if (DEBUG_MODE) {
        echo "<pre style='color:red'>" . htmlspecialchars($e) . "</pre>";
        exit;
    }

    FlashHelper::set('error', 'Full backup failed: ' . $e->getMessage());
    header('Location: ' . BASE_URL . '/system/maintenance');
    exit;
}
