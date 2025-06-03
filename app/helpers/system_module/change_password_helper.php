<?php

use App\Core\DB;
use App\Helpers\Core\FlashHelper;

require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../helpers/core/FlashHelper.php';

$pdo = DB::connect();

$id = (int)($_POST['id'] ?? 0);
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($id < 1 || $password === '' || $password !== $confirmPassword) {
    FlashHelper::set('error', '❌ Passwords do not match or are invalid.');
    header('Location: ' . (BASE_URL ?? '/') . 'system/users');
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
$stmt->execute([
    ':password' => password_hash($password, PASSWORD_DEFAULT),
    ':id'       => $id
]);

FlashHelper::set('success', '✅ Password updated successfully.');
header('Location: ' . (BASE_URL ?? '/') . 'system/users');
exit;
