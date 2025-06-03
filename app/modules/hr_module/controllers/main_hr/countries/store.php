<?php

use App\Core\AppKernel;
use App\Core\DB;

if (!class_exists(AppKernel::class)) {
    require_once dirname(__DIR__, 5) . '/core/AppKernel.php';
    AppKernel::boot();
}

$pdo = DB::connect();
$stmt = $pdo->prepare('INSERT INTO countries (name, iso_code, default_currency_code, local_number_length, base_dial_key, accepted_prefixes, timezone, flag_image, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([
    $_POST['name'],
    $_POST['iso_code'],
    $_POST['default_currency_code'],
    $_POST['local_number_length'],
    $_POST['base_dial_key'],
    $_POST['accepted_prefixes'],
    $_POST['timezone'],
    $_POST['flag_image'],
    isset($_POST['is_active']) ? 1 : 0
]);

header('Location: /hr/countries?added=1');
exit;
