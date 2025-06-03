<?php
// File: /app/ajax/general_module/logger_receiver.php

require_once __DIR__ . '/../../../core/Logger.php';
require_once __DIR__ . '/../../../core/TraceManager.php';
require_once __DIR__ . '/../../../core/LogFormatter.php';
require_once __DIR__ . '/../../../core/LogStorage.php';

use App\Core\Logger;

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['trace_id']) || empty($input['event'])) {
    \App\Core\Logger::trigger(
        'AJAX Logger: Invalid input',
        \App\Helpers\general_module\logmanager\LogContextBuilder::enrich([
            'payload' => file_get_contents('php://input'),
            'user' => $_SESSION['user_name'] ?? 'system',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            'agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
        ]),
        'ERROR',
        'system'
    );
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

\App\Core\Logger::trigger(
    'AJAX Logger: Received log submission',
    \App\Helpers\general_module\logmanager\LogContextBuilder::enrich([
        'payload' => $input,
        'user' => $_SESSION['user_name'] ?? 'system',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
        'agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
    ]),
    'INFO',
    'system'
);

try {
    $_POST['trace_id'] = $input['trace_id'];

    \App\Core\Logger::trigger(
        $input['event'],
        $input['context'] ?? [],
        $input['level'] ?? 'INFO',
        $input['mode'] ?? 'js'
    );

    echo json_encode(['status' => 'success']);
} catch (\Throwable $e) {
    \App\Core\Logger::trigger(
        'AJAX Logger: Exception during log submission',
        \App\Helpers\general_module\logmanager\LogContextBuilder::enrich([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'payload' => $input
        ]),
        'ERROR',
        'system'
    );
    echo json_encode(['status' => 'error', 'message' => 'Log submission failed']);
}
