<?php
// 📁 /app/modules/system_module/controllers/route_builder_save.php

// Boot Kernel if needed
if (!class_exists(\App\Core\AppKernel::class)) {
    require_once dirname(__DIR__, 4) . '/app/core/AppKernel.php';
    \App\Core\AppKernel::boot();
}

use App\Core\AppKernel;
use App\Helpers\Core\FlashHelper;
use App\Helpers\Core\FlashRedirectHelper;
use App\Core\App;

// 🛡️ Input validation
$module = $_POST['module'] ?? '';
$viewsJson = $_POST['views_json'] ?? '[]';
$controllersJson = $_POST['controllers_json'] ?? '[]';

if (!$module || !preg_match('/^[a-zA-Z0-9_]+$/', $module)) {
    FlashRedirectHelper::error('❌ Invalid or missing module name', '/system/router-manager');
}

try {
    $views = json_decode($viewsJson, true) ?? [];
    $controllers = json_decode($controllersJson, true) ?? [];
} catch (Throwable $e) {
    FlashRedirectHelper::error('❌ Invalid JSON data received', '/system/router-manager');
}

$log = [];
$prefix = '/' . str_replace('_module', '', $module);
$modulePath = MODULES_PATH . '/' . $module;
$mapPath = $modulePath . '/controllers/routes.map.php';
$viewFolder = $modulePath . '/views/';
$ctrlFolder = $modulePath . '/controllers/';

// 🔍 Validate routing file existence
if (!file_exists($mapPath)) {
    FlashRedirectHelper::error("❌ Missing routes.map.php in module <code>$module</code>", '/system/router-manager');
}

// 📄 Load route map
$routes = require $mapPath;
if (!is_array($routes)) {
    $routes = ['views' => [], 'controllers' => []];
    $log[] = "⚠️ Malformed map file. Re-initialized.";
}

// --- 1. VIEW HANDLING ---
foreach ($views as $path => $viewTarget) {
    $viewFile = basename($viewTarget);
    $filePath = $viewFolder . $viewFile;

    // Only add if route doesn't exist
    if (!isset($routes['views'][$path])) {
        $routes['views'][$path] = $viewTarget;
        $log[] = "✅ View route added: $path → $viewTarget";
    } else {
        $log[] = "⚠️ View route exists: $path (skipped)";
    }

    if (!file_exists($filePath)) {
        $viewDir = dirname($filePath);
        if (!is_dir($viewDir)) {
            mkdir($viewDir, 0775, true);
            $log[] = "📁 Created view subfolder: " . str_replace($viewFolder, '', $viewDir);
        }

        // Create view file with basic template
        $template = <<<PHP
<?php
/**
 * View: {$path}
 * File: {$viewFile}
 */

use App\Helpers\Core\FlashHelper;

// Page title
\$title = '{$path}';

// Start content
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="page-title"><?= htmlspecialchars(\$title) ?></h1>
            
            <!-- Add your view content here -->
            
        </div>
    </div>
</div>
PHP;
        file_put_contents($filePath, $template);
        $log[] = "✅ View file created: $viewFile";
    } else {
        $log[] = "⚠️ View file already exists: $viewFile";
    }
}

// --- 2. CONTROLLER HANDLING ---
foreach ($controllers as $ctrl) {
    $method = strtolower($ctrl['method'] ?? 'get');
    $path = $ctrl['path'];
    $file = $ctrl['file'];
    
    if (!$file || !$path) continue;

    // Check if controller route already exists
    $exists = false;
    foreach ($routes['controllers'] as $r) {
        if ($r['path'] === $path && $r['file'] === $file && $r['method'] === $method) {
            $exists = true;
            break;
        }
    }

    // Only add if route doesn't exist
    if (!$exists) {
        $routes['controllers'][] = [
            'method' => $method,
            'path' => $path,
            'file' => $file
        ];
        $log[] = "✅ Controller added: [$method] $path → $file";
    } else {
        $log[] = "⚠️ Controller exists: [$method] $path (skipped)";
    }

    $ctrlFilePath = $ctrlFolder . $file;
    if (!file_exists($ctrlFilePath)) {
        $ctrlDir = dirname($ctrlFilePath);
        if (!is_dir($ctrlDir)) {
            mkdir($ctrlDir, 0775, true);
            $log[] = "📁 Created controller subfolder: " . str_replace($ctrlFolder, '', $ctrlDir);
        }

        // Create controller file with basic template
        $template = <<<PHP
<?php
/**
 * Controller: {$path}
 * Method: {$method}
 * File: {$file}
 */

// Boot Kernel if needed
if (!class_exists(\App\Core\AppKernel::class)) {
    require_once dirname(__DIR__, 4) . '/app/core/AppKernel.php';
    \App\Core\AppKernel::boot();
}

use App\Core\AppKernel;
use App\Helpers\Core\FlashHelper;
use App\Core\App;

try {
    // Add your controller logic here
    
    FlashHelper::set('success', 'Operation completed successfully');
} catch (Throwable \$e) {
    FlashHelper::set('error', \$e->getMessage());
}

App::redirect(BASE_URL . '{$path}');
PHP;
        file_put_contents($ctrlFilePath, $template);
        $log[] = "✅ Controller file created: $file";
    } else {
        $log[] = "⚠️ Controller file already exists: $file";
    }
}

// --- 3. WRITE MAP FILE ---
// Sort routes for better readability
ksort($routes['views']); // Sort view routes alphabetically
usort($routes['controllers'], function($a, $b) {
    return strcmp($a['path'], $b['path']);
});

$routesExport = "<?php\n\nreturn " . var_export($routes, true) . ";\n";
file_put_contents($mapPath, $routesExport);
$log[] = "📝 Route map saved to: $mapPath";

// --- 4. REDIRECT WITH FEEDBACK ---
$_SESSION['route_log'] = $log;
FlashRedirectHelper::success('✅ Routes added successfully', '/system/router-manager');
