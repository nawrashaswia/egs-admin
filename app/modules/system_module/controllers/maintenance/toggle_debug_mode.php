<?php
if (!class_exists('App\\Core\\AppKernel')) {
    require_once dirname(__DIR__, 4) . '/core/AppKernel.php';
    \App\Core\AppKernel::boot();
}
use App\Helpers\Core\FlashHelper;

$configPath = CONFIG_PATH . '/app.php';
$config = require $configPath;

$debug = isset($_POST['debug']) && $_POST['debug'] === '1';
$config['debug'] = $debug;

// Write the updated config back to file
$content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
file_put_contents($configPath, $content);

FlashHelper::set(
    'info',
    $debug
        ? 'Debug Mode enabled: Debug info and developer tools are now active.'
        : 'Debug Mode disabled: Debug info and advanced tools are now hidden.'
);

header('Location: ' . BASE_URL . '/system/maintenance');
exit; 