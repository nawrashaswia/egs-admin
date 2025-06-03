<?php

use App\Helpers\Core\FlashHelper;

// ✅ Input
$folders      = $_POST['include_folders'] ?? [];
$filesIn      = $_POST['include_files_in'] ?? [];
$zipRequested = ($_POST['zip_export'] ?? '0') === '1';

try {
    if (empty($folders)) {
        throw new Exception('❌ Please select at least one folder to export.');
    }

    // ✅ Define paths
    $rootPath   = BASE_PATH; // Already points to /www
    $exportDate = date('Y-m-d');
    $timestamp  = date('Y-m-d_His');
    $exportDir  = STORAGE_PATH . "/backups/$exportDate";
    $baseName   = "folder_structure_$timestamp";
    $txtPath    = "$exportDir/$baseName.txt";
    $zipPath    = "$exportDir/$baseName.zip";

    if (!is_dir($exportDir) && !@mkdir($exportDir, 0775, true)) {
        throw new Exception("Failed to create backup directory: $exportDir");
    }

    // ✅ Build TXT Report
    $lines = [
        "📦 EG-ADMIN Folder Export",
        "🕒 Generated: " . date('Y-m-d H:i:s'),
        ""
    ];

    foreach ($folders as $relativePath) {
        $cleanPath = preg_replace('#^www/#', '', $relativePath);
        $absolute  = "$rootPath/$cleanPath";

        if (!is_dir($absolute)) continue;

        $lines[] = "📁 $relativePath/";

        if (in_array($relativePath, $filesIn)) {
            foreach (scandir($absolute) as $file) {
                if ($file === '.' || $file === '..') continue;
                if (is_file("$absolute/$file")) {
                    $lines[] = "  📄 $relativePath/$file";
                }
            }
        }
    }

    file_put_contents($txtPath, implode(PHP_EOL, $lines));

    // ✅ ZIP logic
    if ($zipRequested) {
        $zip = new ZipArchive();
        if (!$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new Exception("❌ Failed to create ZIP archive.");
        }

        $zip->addFile($txtPath, basename($txtPath));

        foreach ($folders as $relativePath) {
            $cleanPath = preg_replace('#^www/#', '', $relativePath);
            $absolute  = "$rootPath/$cleanPath";

            if (!is_dir($absolute)) continue;

            $zip->addEmptyDir($relativePath);

            if (in_array($relativePath, $filesIn)) {
                foreach (scandir($absolute) as $file) {
                    if ($file === '.' || $file === '..') continue;
                    $full = "$absolute/$file";
                    if (is_file($full)) {
                        $zip->addFile($full, "$relativePath/$file");
                    }
                }
            }
        }

        $zip->close();
        FlashHelper::set('success', '✅ ZIP export created: <code>' . basename($zipPath) . '</code>.');
    } else {
        FlashHelper::set('success', '✅ Folder structure exported to <code>' . basename($txtPath) . '</code>.');
    }

    header('Location: ' . BASE_URL . '/system/maintenance');
    exit;

} catch (Throwable $e) {
    if (DEBUG_MODE) {
        echo "<pre style='color:red'>" . htmlspecialchars($e) . "</pre>";
        exit;
    }

    FlashHelper::set('error', '❌ Export failed: ' . $e->getMessage());
    header('Location: ' . BASE_URL . '/system/maintenance');
    exit;
}
