<?php

use App\Core\AppKernel;
use App\Core\DB;
use App\Helpers\Core\FlashRedirectHelper;
use App\Helpers\Core\FlashHelper;

// ✅ Boot the Kernel if not already booted
if (!class_exists(AppKernel::class)) {
    require_once dirname(__DIR__, 4) . '/app/core/AppKernel.php';
    AppKernel::boot();
}

// ✅ Allow only POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$pdo = DB::connect();

// ✅ Sanitize inputs
$ruleName          = trim($_POST['rule_name'] ?? '');
$allowedExtensions = strtolower(trim($_POST['allowed_extensions'] ?? ''));
$maxSize           = intval($_POST['max_size_mb'] ?? 0);
$notes             = trim($_POST['notes'] ?? '');
$isActive          = intval($_POST['is_active'] ?? 1);

// ✅ Validate required fields
if (empty($ruleName) || empty($allowedExtensions) || $maxSize <= 0) {
    FlashHelper::set('error', 'All required fields must be filled properly.');
    header('Location: ' . BASE_URL . '/general/attachment_manager/attachment_rule_add');
    exit;
}

// ✅ Normalize extension list
$newExts = array_unique(array_map('trim', explode(',', $allowedExtensions)));
sort($newExts);

try {
    $stmt = $pdo->query("SELECT id, rule_name, allowed_extensions FROM attachment_rules");
    $existingRules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $warnings = [];

    foreach ($existingRules as $rule) {
        $existingExts = array_unique(array_map('trim', explode(',', strtolower($rule['allowed_extensions']))));
        sort($existingExts);

        if ($existingExts === $newExts) {
            FlashHelper::set('error', "A rule with these exact extensions already exists: <strong>{$rule['rule_name']}</strong>.");
            header('Location: ' . BASE_URL . '/general/attachment_manager/attachment_rule_add');
            exit;
        }

        if (count(array_diff($newExts, $existingExts)) === 0) {
            $warnings[] = "⚠️ These extensions are already included in a broader rule: <strong>{$rule['rule_name']}</strong>.";
        }
    }

    // ✅ Insert rule
    $stmt = $pdo->prepare("INSERT INTO attachment_rules (rule_name, allowed_extensions, max_size_mb, notes, is_active)
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$ruleName, implode(',', $newExts), $maxSize, $notes, $isActive]);

    $message = '✅ Rule added successfully.';
    if (!empty($warnings)) {
        $message .= '<br>' . implode('<br>', $warnings);
    }

FlashRedirectHelper::success($message, '/general/attachment_manager/settings_ui');

} catch (Throwable $e) {
    FlashRedirectHelper::error("Failed to save rule: " . $e->getMessage(), '/general/attachment_manager');
}
