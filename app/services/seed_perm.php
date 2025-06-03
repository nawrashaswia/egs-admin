<?php
// path: www/app/services/seed_perm.php

require_once __DIR__ . '/../core/AppKernel.php';

use App\Core\AppKernel;
use App\Core\DB;
use App\Services\PermissionSeeder;

// 🔥 Boot the kernel (defines CONFIG_PATH, loads config, sets App::set(), etc.)
AppKernel::boot();

// 👑 Use your booted PDO connection via your own system
$pdo = DB::connect();

// ✅ Run the permission seeder
$seeder = new PermissionSeeder($pdo);
$inserted = $seeder->run();

// 📣 Output what was inserted
echo "✅ Inserted " . count($inserted) . " new permissions:\n";
foreach ($inserted as $key) {
    echo " - $key\n";
}
