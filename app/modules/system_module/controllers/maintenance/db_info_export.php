<?php

use App\Core\DB;
use App\Helpers\Core\FlashHelper;

try {
    // ✅ Connect to DB
    $pdo = DB::connect();

    // ✅ Load DB config to get the DB name
    $config = require CONFIG_PATH . '/database.php';
    $dbName = $config['name'];

    // ✅ Get charset
    $charset = $pdo->query("SELECT @@character_set_database")->fetchColumn();

    // ✅ Setup file path
    $today     = date('Y-m-d');
    $timestamp = date('Y-m-d_His');
    $exportDir = STORAGE_PATH . "/backups/$today";
    $fileName  = "database_info_$timestamp.txt";
    $filePath  = "$exportDir/$fileName";

    if (!is_dir($exportDir) && !@mkdir($exportDir, 0775, true)) {
        throw new Exception("Failed to create export directory: $exportDir");
    }

    // ✅ Start export content
    $output = [
        "🗄️ EG-ADMIN DATABASE STRUCTURE",
        "🕒 Generated: " . date('Y-m-d H:i:s'),
        "",
        "📛 Database: $dbName",
        "🔤 Charset: $charset",
        "",
        str_repeat("═", 40),
        ""
    ];

    // ✅ Gather table and structure info
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $output[] = "📂 Table: `$table`";
        $output[] = str_repeat("-", 35);

        $cols = $pdo->query("SHOW FULL COLUMNS FROM `$table`")->fetchAll();
        foreach ($cols as $col) {
            $line = "🧱 {$col['Field']} — {$col['Type']}";
            $line .= $col['Null'] === 'NO' ? " NOT NULL" : " NULL";
            if ($col['Default'] !== null) $line .= " DEFAULT '{$col['Default']}'";
            if ($col['Extra']) $line .= " [{$col['Extra']}]";
            $output[] = $line;
        }

        // ✅ Foreign keys
        $stmt = $pdo->prepare("
            SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tbl AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $stmt->execute(['db' => $dbName, 'tbl' => $table]);
        $foreignKeys = $stmt->fetchAll();

        if ($foreignKeys) {
            $output[] = "";
            $output[] = "🔗 Foreign Keys:";
            foreach ($foreignKeys as $fk) {
                $output[] = "  🔸 {$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}";
            }
        }

        $output[] = "";
    }

    // ✅ Save file
    file_put_contents($filePath, implode(PHP_EOL, $output));

    // ✅ Set flash and redirect
    FlashHelper::set('success', ' DB structure exported to <code>' . $fileName . '</code>.');
    header("Location: " . BASE_URL . "/system/maintenance");
    exit;

} catch (Throwable $e) {
    if (DEBUG_MODE) {
        echo "<pre style='color:red'>" . htmlspecialchars($e) . "</pre>";
        exit;
    }

    FlashHelper::set('error', ' Export failed: ' . $e->getMessage());
    header("Location: " . BASE_URL . "/system/maintenance");
    exit;
}
