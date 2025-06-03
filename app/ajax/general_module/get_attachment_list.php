<?php

use App\Core\AppKernel;
use App\Core\DB;

// ✅ Boot AppKernel if not already done (safe)
if (!class_exists(AppKernel::class)) {
    require_once __DIR__ . '/../../core/AppKernel.php';
    AppKernel::boot();
}

$ref = $_GET['ref'] ?? null;
if (!$ref) {
    echo "<div class='text-danger'>Missing reference number.</div>";
    exit;
}

try {
    $pdo = DB::connect();
    $stmt = $pdo->prepare("SELECT * FROM attachments WHERE reference_number = ? AND is_deleted = 0 ORDER BY uploaded_at DESC");
    $stmt->execute([$ref]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo "<div class='text-danger'>Failed to fetch attachments.</div>";
    exit;
}

if (empty($files)) {
    echo "<div id='attachment-empty-msg' class='text-secondary fst-italic text-center py-3' style='font-size: 1.01em; background: #f6f8fa; border-radius: 0.5rem;'>
            <i class='ti ti-folder-open me-1'></i> No files uploaded yet.
          </div>";
    exit;
}

foreach ($files as $file) {
    $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
    $iconClass = htmlspecialchars("ti ti-file-type-{$ext}");
    $safeName = htmlspecialchars($file['file_name']);
    $downloadUrl = htmlspecialchars("/uploads/attachments/{$file['module']}/{$file['reference_number']}/{$file['file_name']}");

    echo "
    <div class='attachment-row d-flex align-items-center justify-content-between mb-1'>
        <div class='d-flex align-items-center gap-2' style='min-width:0;'>
            <i class='{$iconClass}'></i>
            <span class='attachment-filename' title='{$safeName}'>{$safeName}</span>
        </div>
        <a href='{$downloadUrl}' class='btn btn-sm btn-outline-secondary' target='_blank' data-bs-toggle='tooltip' title='Download'>
            <i class='ti ti-download'></i>
        </a>
    </div>";
}

try {
    // Get attachments
    $attachments = $attachmentManager->getAttachments($ref);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'data' => $attachments
    ]);
    
} catch (Throwable $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch attachments'
    ]);
}
