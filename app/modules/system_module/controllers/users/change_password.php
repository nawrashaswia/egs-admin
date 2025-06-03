<?php

use App\Core\DB;
use App\Core\AppKernel;
use App\Helpers\Core\CSRFHelper;
use App\Helpers\Core\FlashRedirectHelper;
use App\Helpers\Core\ValidationHelper;

// Boot kernel if needed
if (!class_exists(AppKernel::class)) {
    require_once dirname(__DIR__, 4) . '/app/core/AppKernel.php';
    AppKernel::boot();
}

$pdo = DB::connect();

// 🧼 Input
$id              = (int)($_POST['id'] ?? 0);
$password        = trim($_POST['password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

// ❌ ID Check
if ($id < 1) {
    FlashRedirectHelper::error('Invalid user ID.', '/system/users');
}

// 🛡️ CSRF Protection
if (!CSRFHelper::validateToken($_POST['csrf_token'] ?? '', 'change_password_' . $id)) {
    FlashRedirectHelper::error('Invalid CSRF token.', '/system/users');
}

// 🚫 Validate inputs (excluding match)
$validationRules = [
    'password' => [['required', 'Password is required']],
    'confirm_password' => [['required', 'Please confirm your password']],
];
$errors = ValidationHelper::errors($validationRules, $_POST);

// 🧠 Manually validate password match
if ($password !== $confirmPassword) {
    $errors['confirm_password'][] = 'Passwords do not match.';
}

// 🚨 If any validation errors
if (!empty($errors)) {
    $errorMessages = [];

    foreach ($errors as $fieldErrors) {
        if (is_array($fieldErrors)) {
            foreach ($fieldErrors as $error) {
                if (is_string($error)) {
                    $errorMessages[] = $error;
                }
            }
        } elseif (is_string($fieldErrors)) {
            $errorMessages[] = $fieldErrors;
        }
    }

    if (empty($errorMessages)) {
        $errorMessages[] = 'Invalid input.';
    }

    FlashRedirectHelper::error(
        'Password validation failed: ' . implode('; ', $errorMessages),
        '/system/users'
    );
}

// ✅ Update password
try {
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    $stmt->execute([
        ':password' => password_hash($password, PASSWORD_DEFAULT),
        ':id' => $id
    ]);

    if ($stmt->rowCount() === 0) {
        FlashRedirectHelper::error('User not found or password unchanged.', '/system/users');
    }

    FlashRedirectHelper::success('✅ Password updated successfully.', '/system/users');

} catch (PDOException $e) {
    FlashRedirectHelper::error('❌ Failed to update password: ' . $e->getMessage(), '/system/users');
}
