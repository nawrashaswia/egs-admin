<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🌍 Boot kernel
require_once __DIR__ . '/../app/core/AppKernel.php';
require_once __DIR__ . '/../app/helpers/general_module/logmanager/PerformanceLogManager.php';

// 🧠 Start performance timer
use App\Helpers\General_Module\LogManager\PerformanceLogManager;
PerformanceLogManager::start();

// ⏳ Register shutdown logging
register_shutdown_function(fn() => PerformanceLogManager::end());

use App\Core\AppKernel;
use App\Core\Router;
use App\Core\RouteLoader;

// 🔧 Start system
AppKernel::boot();

// 📦 Load module-defined routes
RouteLoader::loadAll();

// 🚀 Dispatch matched route
Router::dispatch();
