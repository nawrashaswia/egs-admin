<?php

use App\Core\DB;
use App\Services\PermissionSeeder;

require_once BASE_PATH . '/app/core/AppKernel.php';
App\Core\AppKernel::boot();

header('Content-Type: application/json');

try {
    $pdo = DB::connect();
    $seeder = new PermissionSeeder($pdo);
    $inserted = $seeder->run();

    echo json_encode([
        'status' => 'success',
        'inserted_count' => count($inserted),
        'inserted_keys' => $inserted
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);
}
