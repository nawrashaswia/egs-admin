<?php
// Manual app context log test
require_once __DIR__ . '/../app/core/AppKernel.php';
require_once __DIR__ . '/../app/core/Logger.php';
require_once __DIR__ . '/../app/core/LogFormatter.php';
require_once __DIR__ . '/../app/core/TraceManager.php';
require_once __DIR__ . '/../app/core/DB.php';

use App\Core\AppKernel;
use App\Core\Logger;

// Boot the app (loads config, session, etc.)
AppKernel::boot();

Logger::trigger('Manual test log from manual_app_log_test.php', ['manual' => true], 'INFO', 'system');
echo "<b>Manual Logger::trigger() with 'system' mode called in app context.</b><br>";
echo "<hr><b>Done.</b>"; 