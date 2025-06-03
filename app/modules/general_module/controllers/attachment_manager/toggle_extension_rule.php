<?php

use App\Core\AppKernel;
use App\Helpers\Core\FlashRedirectHelper;
use App\Core\DB;

// Boot Kernel if needed
if (!class_exists(AppKernel::class)) {
    require_once dirname(__DIR__, 4) . '/app/core/AppKernel.php';
    AppKernel::boot();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    FlashRedirectHelper::error('Rule ID is missing.', '/general/attachment_manager/settings_ui');
    exit;
}

try {
    $pdo = DB::connect();

    $stmt = $pdo->prepare("SELECT is_active, rule_name FROM attachment_rules WHERE id = ?");
    $stmt->execute([$id]);
    $rule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rule) {
        FlashRedirectHelper::error('Rule not found.', '/general/attachment_manager/settings_ui');
        exit;
    }

    $newStatus = $rule['is_active'] ? 0 : 1;

    $pdo->prepare("UPDATE attachment_rules SET is_active = ? WHERE id = ?")
        ->execute([$newStatus, $id]);

    $statusText = $newStatus ? 'activated' : 'deactivated';
    FlashRedirectHelper::success('Rule <strong>' . htmlspecialchars($rule['rule_name']) . '</strong> has been ' . $statusText . '.', '/general/attachment_manager/settings_ui');
} catch (Throwable $e) {
    FlashRedirectHelper::error('Failed to toggle rule status: ' . $e->getMessage(), '/general/attachment_manager/settings_ui');
}
