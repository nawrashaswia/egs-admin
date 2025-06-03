<?php

use App\Core\AppKernel;
use Modules\hr_module\Controllers\main_hr\Countries;

if (!class_exists(AppKernel::class)) {
    require_once dirname(__DIR__, 5) . '/core/AppKernel.php';
    AppKernel::boot();
}

require_once __DIR__ . '/../countries.php';

Countries::delete();
