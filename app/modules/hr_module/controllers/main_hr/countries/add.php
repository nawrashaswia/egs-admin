<?php

use App\Core\AppKernel;

if (!class_exists(AppKernel::class)) {
    require_once dirname(__DIR__, 5) . '/core/AppKernel.php';
    AppKernel::boot();
}

require APP_PATH . '/modules/hr_module/views/main_hr/countries/add_countries.php';
