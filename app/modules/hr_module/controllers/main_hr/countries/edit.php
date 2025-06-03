<?php

use App\Core\AppKernel;
use App\Core\DB;

if (!class_exists(AppKernel::class)) {
    require_once dirname(__DIR__, 5) . '/core/AppKernel.php';
    AppKernel::boot();
}

$id = (int)($_GET['id'] ?? 0);
$pdo = DB::connect();
$stmt = $pdo->prepare('SELECT * FROM countries WHERE id = ?');
$stmt->execute([$id]);
$country = $stmt->fetch(\PDO::FETCH_ASSOC);

require APP_PATH . '/modules/hr_module/views/main_hr/countries/edit_countries.php';
