<?php

use App\Core\App;
use App\Helpers\Core\FlashHelper;

// ✅ Initialize AppKernel
require_once dirname(__DIR__, 4) . '/core/AppKernel.php';


try {
    $rootPath   = BASE_PATH;
    $today      = date('Y-m-d');
    $timestamp  = date('Y-m-d_His');
    $backupDir  = "$rootPath/egs-admin/storage/backups/$today";
    $zipName    = "full_www_backup_{$timestamp}.zip";
    $zipPath    = "$backupDir/$zipName";
    $publicLink = BASE_URL . "/storage/backups/$today/$zipName";

    // ✅ Ensure backup directory exists
    if (!is_dir($backupDir) && !mkdir($backupDir, 0775, true)) {
        throw new RuntimeException("❌ Failed to create backup directory: $backupDir");
    }

    $excludedPaths = [realpath($backupDir)];

    $zip = new ZipArchive();
    if (!$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        throw new RuntimeException("❌ Could not create ZIP archive at $zipPath");
    }

    $baseLen = strlen($rootPath) + 1;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        $filePath = $file->getRealPath();
        if (!$filePath || !is_file($filePath)) continue;

        foreach ($excludedPaths as $excluded) {
            if (strpos($filePath, $excluded) === 0) {
                continue 2;
            }
        }

        $relativePath = 'www/' . substr($filePath, $baseLen);
        $zip->addFile($filePath, $relativePath);
    }

    $zip->close();

    $_SESSION['db_summary'] = <<<TEXT
✔️ Action: Full System Backup
📅 Time: {$timestamp}
📁 Contents: All /www files (excluding backup dir)
🗜 File: {$zipName}
🔗 <a href="{$publicLink}" target="_blank">Download ZIP</a>
TEXT;

    FlashHelper::set('warning', "✅ Full system backup created: <code>$zipName</code>.");
    header("Location: " . BASE_URL . "/system/maintenance");
    exit;

} catch (Throwable $e) {
    FlashHelper::set('error', '❌ Full backup failed: ' . $e->getMessage());
    header("Location: " . BASE_URL . "/system/maintenance");
    exit;
}
