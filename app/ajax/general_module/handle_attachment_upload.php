<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// File: app/ajax/general_module/handle_attachment_upload.php

use app\Core\AppKernel;
use App\Helpers\General_Module\AttachmentManagerHelper;

// ✅ Boot the system if not already booted
if (!class_exists(AppKernel::class)) {
    require_once __DIR__ . '/../../core/AppKernel.php';
    AppKernel::boot();
}

// ✅ Ensure JSON response
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Only POST requests are allowed.");
    }

    if (empty($_FILES['file']['tmp_name'])) {
        throw new Exception("No file uploaded.");
    }

    $ref      = $_POST['reference_number'] ?? '';
    $filename = $_POST['custom_filename'] ?? '';
    $ruleId   = isset($_POST['rule_id']) ? (int)$_POST['rule_id'] : null;

    if (!$ref || !$filename) {
        throw new Exception("Missing reference number or filename.");
    }

    $file = $_FILES['file'];

    $result = AttachmentManagerHelper::saveFile($ref, $filename, $file, $ruleId);
    echo json_encode($result);

} catch (Throwable $e) {
    // Log error if manager available
    if (class_exists(AttachmentManagerHelper::class)) {
        AttachmentManagerHelper::logError("❌ Upload Error: " . $e->getMessage());
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
