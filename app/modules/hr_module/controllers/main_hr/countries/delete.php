<?php

use App\Core\AppKernel;
use App\Core\DB;

if (!class_exists(AppKernel::class)) {
    require_once dirname(__DIR__, 5) . '/core/AppKernel.php';
    AppKernel::boot();
}

$id = (int)($_GET['id'] ?? 0);
$pdo = DB::connect();
$stmt = $pdo->prepare('DELETE FROM countries WHERE id = ?');
$stmt->execute([$id]);

header('Location: /hr/countries?deleted=1');
exit;
