<?php

use App\Core\AppKernel;
use App\Core\DB;
use App\Helpers\Core\FlashRedirectHelper;

// 🧠 Boot Kernel if not already active
if (!class_exists(AppKernel::class)) {
    require_once dirname(__DIR__, 4) . '/core/AppKernel.php';
    AppKernel::boot();
}

// 🛡️ Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// 📦 DB connection
$pdo = DB::connect();

// 🔎 Input sanitization
$id                = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$ruleName          = trim($_POST['rule_name'] ?? '');
$allowedExtensions = strtolower(trim($_POST['allowed_extensions'] ?? ''));
$maxSize           = filter_input(INPUT_POST, 'max_size_mb', FILTER_VALIDATE_INT);
$notes             = trim($_POST['notes'] ?? '');
$isActive          = filter_input(INPUT_POST, 'is_active', FILTER_VALIDATE_INT) ?? 1;

// 🚨 Validation
if (!$id || !$ruleName || !$allowedExtensions || !$maxSize || $maxSize <= 0) {
    FlashRedirectHelper::error(
        'Please fill in all required fields correctly.',
        "/general/attachment_manager/edit_extensions_ui?id=$id"
    );
    exit;
}

try {
    // ✅ Confirm rule exists
    $stmt = $pdo->prepare("SELECT id FROM attachment_rules WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        FlashRedirectHelper::error('Rule not found.', '/general/attachment_manager/settings_ui');
        exit;
    }

    // 🚫 Check for duplicate name
    $stmt = $pdo->prepare("SELECT id FROM attachment_rules WHERE rule_name = ? AND id != ?");
    $stmt->execute([$ruleName, $id]);
    if ($stmt->fetch()) {
        FlashRedirectHelper::error(
            'A rule with this name already exists.',
            "/general/attachment_manager/edit_extensions_ui?id=$id"
        );
        exit;
    }

    // 🔁 Normalize extensions
    $extensions = array_unique(array_map('trim', explode(',', $allowedExtensions)));
    sort($extensions);
    $normalizedExtensions = implode(',', $extensions);

    // 💾 Update
    $stmt = $pdo->prepare("
        UPDATE attachment_rules SET 
            rule_name = ?, 
            allowed_extensions = ?, 
            max_size_mb = ?, 
            notes = ?, 
            is_active = ?, 
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");

    $stmt->execute([
        $ruleName,
        $normalizedExtensions,
        $maxSize,
        $notes,
        $isActive,
        $id
    ]);

    FlashRedirectHelper::success('Rule updated successfully.', '/general/attachment_manager/settings_ui');
} catch (Throwable $e) {
    FlashRedirectHelper::error(
        'Failed to update rule: ' . $e->getMessage(),
        "/general/attachment_manager/edit_extensions_ui?id=$id"
    );
}
