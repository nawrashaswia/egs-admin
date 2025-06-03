<?php

use App\Core\DB;
use App\Helpers\Core\CSRFHelper;
use App\Helpers\Core\FlashRedirectHelper;
use App\Helpers\Core\ValidationHelper;

// Boot Kernel if needed (optional if run via router)
if (!class_exists(\App\Core\AppKernel::class)) {
    require_once dirname(__DIR__, 4) . '/app/core/AppKernel.php';
    \App\Core\AppKernel::boot();
}

$pdo = DB::connect();

// 🧼 Sanitize input
$id         = (int)($_POST['id'] ?? 0);
$username   = trim($_POST['username'] ?? '');
$full_name  = trim($_POST['full_name'] ?? '');
$password   = $_POST['password'] ?? '';
$role       = $_POST['role'] ?? 'User';
$status     = $_POST['status'] ?? 'Active';

// 🚫 Validate input
$validationRules = [
    'username' => [['required'], ['min', 3], ['max', 32]],
    'role'     => [['required'], ['in', ['Admin', 'Editor', 'User']]],
    'status'   => [['required'], ['in', ['Active', 'Inactive']]],
];

$errors = ValidationHelper::errors($validationRules, $_POST);
if (!empty($errors)) {
    FlashRedirectHelper::error('Validation failed. Please check your input.', '/system/users');
}

if ($id < 1 || empty($username)) {
    FlashRedirectHelper::error('Invalid user data.', '/system/users');
}

// 🛡️ CSRF Protection
if (!CSRFHelper::validateToken($_POST['csrf_token'] ?? '', 'users_edit')) {
    FlashRedirectHelper::error('Invalid CSRF token.', '/system/users');
}

// 🧠 Fetch current user to get existing avatar (for cleanup)
$stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->execute([$id]);
$currentUser = $stmt->fetch();

$currentAvatar = $currentUser['avatar'] ?? null;
$newAvatar = $currentAvatar;

// 🖼️ Handle avatar upload
if (!empty($_FILES['avatar']['name'])) {
    $uploadDir  = PUBLIC_PATH . '/uploads/avatars/';
    $ext        = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $safeName   = uniqid('avatar_', true) . '.' . strtolower($ext);
    $destPath   = $uploadDir . $safeName;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destPath)) {
        $newAvatar = $safeName;

        // Optional: remove old avatar if not default
        if (!empty($currentAvatar) && file_exists($uploadDir . $currentAvatar)) {
            unlink($uploadDir . $currentAvatar);
        }
    } else {
        FlashRedirectHelper::error('❌ Failed to upload new avatar.', '/system/users/edit?id=' . $id);
    }
}

// ✅ Update user
try {
    $query = "UPDATE users SET 
        username = :username,
        full_name = :full_name,
        role = :role,
        status = :status,
        avatar = :avatar";

    $params = [
        ':username'  => $username,
        ':full_name' => $full_name ?: null,
        ':role'      => $role,
        ':status'    => $status,
        ':avatar'    => $newAvatar,
        ':id'        => $id
    ];

    if (!empty($password)) {
        $query .= ", password = :password";
        $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    $query .= " WHERE id = :id";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    if ($stmt->rowCount() === 0) {
        FlashRedirectHelper::error('No changes were made.', '/system/users');
    }

    FlashRedirectHelper::success('✅ User updated successfully.', '/system/users');

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        FlashRedirectHelper::error('Username already exists.', '/system/users/edit?id=' . $id);
    }
    FlashRedirectHelper::error('❌ Error while updating user.', '/system/users');
}
