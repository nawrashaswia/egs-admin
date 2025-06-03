<?php
// File: /app/modules/general_module/controllers/logmanager/toggle_trace_mode.php

use App\Helpers\Core\FlashHelper;

require_once dirname(__DIR__, 5) . '/app/core/AppKernel.php';
\App\Core\AppKernel::boot();

$configPath = CONFIG_PATH . '/app.php';
$config = file_exists($configPath) ? require $configPath : [];

$current = $config['trace_mode'] ?? false;
$config['trace_mode'] = !$current;

// 🔧 Clear active trace session if disabling
if (!$config['trace_mode']) {
    unset($_SESSION['trace_id']);
}

// Save updated config
$configContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
file_put_contents($configPath, $configContent, LOCK_EX);

// 🧹 Remove session override so config drives future decisions
unset($_SESSION['trace_mode']);

// ✅ Log the toggle
\App\Core\Logger::trigger(
    $config['trace_mode'] ? '🟢 Trace mode turned ON' : '🔴 Trace mode turned OFF',
    [
        'user' => $_SESSION['user_name'] ?? 'system',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
        'origin' => 'toggle_trace_mode.php'
    ],
    'INFO',
    'system',
    'formal',
    'short'
);



// ✅ Flash message
$label = $config['trace_mode'] ? 'enabled 🛠' : 'disabled 🛑';
FlashHelper::set('success', "Tracing mode has been <strong>{$label}</strong>.");

// 🚀 Redirect back to UI
header('Location: ' . BASE_URL . '/general/logmanager');
exit;
