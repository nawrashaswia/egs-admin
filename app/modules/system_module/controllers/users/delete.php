<?php

use App\Core\DB;
use App\Helpers\Core\FlashRedirectHelper;

// Boot Kernel if needed (optional if run via router)
if (!class_exists(\App\Core\AppKernel::class)) {
    require_once dirname(__DIR__, 4) . '/app/core/AppKernel.php';
    \App\Core\AppKernel::boot();
}

$pdo = DB::connect();

// 🧼 Sanitize input
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 🚫 Validate input
if ($id < 1) {
    FlashRedirectHelper::error('Invalid user ID.', '/system/users');
}

// 🛡️ Prevent super admin deletion
if ($id === 1) {
    FlashRedirectHelper::warning('Super Admin account cannot be deleted.', '/system/users');
}

// 🧠 Get user's avatar before deletion
$stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

$avatar = $user['avatar'] ?? null;
$avatarPath = $avatar ? PUBLIC_PATH . '/uploads/avatars/' . $avatar : null;

// ✅ Delete user
try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);

    // 🧹 Delete avatar file if exists and not default
    if ($avatar && file_exists($avatarPath)) {
        unlink($avatarPath);
    }

    FlashRedirectHelper::success('✅ User deleted successfully.', '/system/users');

} catch (PDOException $e) {
    FlashRedirectHelper::error('❌ Failed to delete user.', '/system/users');
}
