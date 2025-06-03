<?php
if (!class_exists('App\\Core\\AppKernel')) {
    require_once dirname(__DIR__, 4) . '/core/AppKernel.php';
    \App\Core\AppKernel::boot();
}
use App\Helpers\Core\FlashHelper;

$configPath = CONFIG_PATH . '/app.php';
$config = require $configPath;

$devMode = isset($_POST['dev_mode']) && $_POST['dev_mode'] === '1';
$config['dev_mode'] = $devMode;

// Write the updated config back to file
$content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
file_put_contents($configPath, $content);

FlashHelper::set(
    'info',
    $devMode
        ? 'Dev Mode enabled: Debug info and developer tools are now active.'
        : 'Dev Mode disabled: Debug info and advanced tools are now hidden.'
);

header('Location: ' . BASE_URL . '/system/maintenance');
exit; 