<?php

use App\Core\AppKernel;
use App\Core\DB;
use App\Helpers\Core\FlashRedirectHelper;

// 🚀 Boot Kernel if not already done
if (!class_exists(AppKernel::class)) {
    require_once dirname(__DIR__, 4) . '/core/AppKernel.php';
    AppKernel::boot();
}

// 🆔 Rule ID from URL
$id = $_GET['id'] ?? null;

// ❗ No ID provided
if (!$id) {
    FlashRedirectHelper::error('Missing rule ID for deletion.', '/general/attachment_manager/settings_ui');
    exit;
}

try {
    $pdo = DB::connect();

    // 🔍 Check if rule exists
    $stmt = $pdo->prepare("SELECT rule_name FROM attachment_rules WHERE id = ?");
    $stmt->execute([$id]);
    $rule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rule) {
        FlashRedirectHelper::error('Rule not found.', '/general/attachment_manager/settings_ui');
        exit;
    }

    // 🧹 Delete rule
    $pdo->prepare("DELETE FROM attachment_rules WHERE id = ?")->execute([$id]);

    FlashRedirectHelper::success(
        'Rule <strong>' . htmlspecialchars($rule['rule_name']) . '</strong> deleted.',
        '/general/attachment_manager/settings_ui'
    );
} catch (Throwable $e) {
    FlashRedirectHelper::error(
        'Failed to delete rule: ' . $e->getMessage(),
        '/general/attachment_manager/settings_ui'
    );
}
