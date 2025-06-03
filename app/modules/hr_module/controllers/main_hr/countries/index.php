<?php

use App\Core\AppKernel;
use App\Core\DB;

if (!class_exists(AppKernel::class)) {
    require_once dirname(__DIR__, 5) . '/core/AppKernel.php';
    AppKernel::boot();
}

$pdo = DB::connect();
$stmt = $pdo->query('SELECT * FROM countries ORDER BY name');
$countries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

require APP_PATH . '/modules/hr_module/views/main_hr/countries/index_countries.php';
