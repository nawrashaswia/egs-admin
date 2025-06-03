<?php
// ✅ Force JSON response
header('Content-Type: application/json');

if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/../../../../../..')); // /www
}

try {
    // 🚫 Block accidental GET requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'status' => 'ignored',
            'message' => '✅ This endpoint is alive. Use POST with JSON body to save.'
        ]);
        exit;
    }

    // 🔄 Parse incoming JSON
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        throw new Exception('❌ Invalid JSON body.');
    }

    $folders = $data['include_folders'] ?? [];
    $files   = $data['include_files_in'] ?? [];

    // ✅ Validate save path
    $savePath = BASE_PATH . '/storage/system/folder_export_save.json';
    $dir = dirname($savePath);
    if (!is_dir($dir) || !is_writable($dir)) {
        throw new Exception("❌ Cannot write to directory: $dir");
    }

    // ✅ Encode clean JSON
    $json = json_encode([
        'include_folders' => array_values($folders),
        'include_files_in' => array_values($files)
    ], JSON_PRETTY_PRINT);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('❌ JSON encode failed: ' . json_last_error_msg());
    }

    // ✅ Write to disk
    $bytes = file_put_contents($savePath, $json);
    if ($bytes === false) {
        throw new Exception('❌ Failed to write save file.');
    }

    echo json_encode([
        'status' => 'success',
        'bytes_written' => $bytes
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage()
    ]);
}
