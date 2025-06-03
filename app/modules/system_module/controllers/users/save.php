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
$username   = trim($_POST['username'] ?? '');
$full_name  = trim($_POST['full_name'] ?? '');
$password   = $_POST['password'] ?? '';
$role       = $_POST['role'] ?? 'User';
$status     = $_POST['status'] ?? 'Active';

// 🚫 Validate input
$validationRules = [
    'username' => [['required'], ['min', 3], ['max', 32]],
    'password' => [['required'], ['min', 4]],
    'role'     => [['required'], ['in', ['Admin', 'Editor', 'User']]],
    'status'   => [['required'], ['in', ['Active', 'Inactive']]],
];

$errors = ValidationHelper::errors($validationRules, $_POST);
if (!empty($errors)) {
    FlashRedirectHelper::error('Validation failed. Please check your input.', '/system/users/add');
}

// 🔐 Password hash
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// 🖼️ Handle avatar upload
$avatarFileName = null;
if (!empty($_FILES['avatar']['name'])) {
    $uploadDir = PUBLIC_PATH . '/uploads/avatars/';
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $safeName = uniqid('avatar_', true) . '.' . strtolower($ext);
    $destPath = $uploadDir . $safeName;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destPath)) {
        $avatarFileName = $safeName;
    } else {
        FlashRedirectHelper::error('❌ Failed to upload profile picture.', '/system/users/add');
    }
}

// 🛡️ CSRF Protection
if (!CSRFHelper::validateToken($_POST['csrf_token'] ?? '', 'users_add')) {
    FlashRedirectHelper::error('Invalid CSRF token.', '/system/users/add');
}

// ✅ Save user
try {
    $stmt = $pdo->prepare("
        INSERT INTO users (username, full_name, password, role, status, avatar, created_at)
        VALUES (:username, :full_name, :password, :role, :status, :avatar, NOW())
    ");

    $stmt->execute([
        ':username'  => $username,
        ':full_name' => $full_name ?: null,
        ':password'  => $hashedPassword,
        ':role'      => $role,
        ':status'    => $status,
        ':avatar'    => $avatarFileName
    ]);

    if ($stmt->rowCount() === 0) {
        die(" Insert failed — no rows affected.");
    }

    FlashRedirectHelper::success(' User created successfully.', '/system/users');

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        FlashRedirectHelper::error(' Username already exists.', '/system/users/add');
    }

    // Dev-only fallback
    die(" DB ERROR: " . $e->getMessage());
}
