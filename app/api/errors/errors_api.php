<?php
// app/api/errors/errors_api.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$uuid       = $_POST['uuid']       ?? null;
$reason     = $_POST['reason']     ?? '';
$diagnosis  = $_POST['diagnosis']  ?? '';

if (!$uuid || !$reason || !$diagnosis) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required data.']);
    exit;
}

$decodedDiagnosis = base64_decode($diagnosis);

$entry = [
    'uuid'      => $uuid,
    'timestamp' => date('Y-m-d H:i:s'),
    'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'reason'    => $reason,
    'diagnosis' => $decodedDiagnosis,
];

$logDir = APP_PATH . '/logs';
$logFile = $logDir . '/user-reports.log';
if (!is_dir($logDir)) mkdir($logDir, 0777, true);

file_put_contents($logFile, print_r($entry, true) . "\n---\n", FILE_APPEND);

http_response_code(200);
echo json_encode(['success' => true, 'message' => 'Thanks for reporting this issue.']);
exit;