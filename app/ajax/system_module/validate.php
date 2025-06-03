<?php
// File: app/ajax/system_module/validate.php

// Prevent any output before we start
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, we'll handle them

try {
    // Boot the kernel
    require_once dirname(__DIR__, 2) . '/core/AppKernel.php';
    \App\Core\AppKernel::boot();

    require_once HELPERS_PATH . '/core/ModuleDiscoveryHelper.php';
    require_once HELPERS_PATH . '/core/JsonResponse.php';

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $module       = $_POST['module'] ?? '';
    $viewPath     = trim($_POST['view_path'] ?? '', '/');
    $viewFile     = trim($_POST['view_file'] ?? '');
    $ctrlPath     = trim($_POST['ctrl_path'] ?? '');
    $ctrlFile     = trim($_POST['ctrl_file'] ?? '');
    $ctrlMethod   = strtolower(trim($_POST['ctrl_method'] ?? 'get'));

    $response = ['status' => 'ok', 'issues' => []];

    // 🛡️ Basic validation
    if (!$module || !preg_match('/^[a-zA-Z0-9_]+$/', $module)) {
        throw new Exception('Invalid or missing module');
    }

    $prefix      = '/' . str_replace('_module', '', $module);
    $modulePath  = MODULES_PATH . '/' . $module;
    $mapPath     = $modulePath . '/controllers/routes.map.php';
    $viewFolder  = $modulePath . '/views/';
    $ctrlFolder  = $modulePath . '/controllers/';

    // 🗺️ Load route map
    $routes = [];
    if (file_exists($mapPath)) {
        $routes = require $mapPath;
        if (!is_array($routes)) {
            $routes = ['views' => [], 'controllers' => []];
        }
    } else {
        $routes = ['views' => [], 'controllers' => []];
    }

    // Ensure routes has the expected structure
    $routes['views'] = $routes['views'] ?? [];
    $routes['controllers'] = $routes['controllers'] ?? [];

    // ✅ Check view route
    if ($viewPath && $viewFile) {
        $fullViewRoute = $prefix . '/' . $viewPath;

        if (isset($routes['views'][$fullViewRoute])) {
            $response['issues'][] = "⚠️ View route already exists: <code>$fullViewRoute</code>";
        }

        if (file_exists($mapPath)) {
            $lines = file($mapPath);
            if ($lines !== false) {
                foreach ($lines as $i => $line) {
                    if (str_contains($line, $fullViewRoute)) {
                        $lineNum = $i + 1;
                        $response['issues'][] = "📍 Route defined in <b>routes.map.php</b> line <code>$lineNum</code>";
                        break;
                    }
                }
            }
        }

        if (file_exists($viewFolder . $viewFile)) {
            $url = "/__dev/open?path=" . urlencode($viewFolder . $viewFile);
            $response['issues'][] = "⚠️ View file already exists: <a href='$url' target='_blank'>$viewFile</a>";
        }
    }

    // ✅ Check controller route
    if ($ctrlPath && $ctrlFile) {
        $fullCtrlRoute = $prefix . '/' . $ctrlPath;

        foreach ($routes['controllers'] as $entry) {
            if (!is_array($entry)) continue;
            
            if (
                ($entry['path'] ?? '') === $fullCtrlRoute &&
                ($entry['file'] ?? '') === $ctrlFile &&
                ($entry['method'] ?? '') === $ctrlMethod
            ) {
                $response['issues'][] = "⚠️ Controller already exists: [<b>$ctrlMethod</b>] <code>$fullCtrlRoute</code>";
            }
        }

        if (file_exists($ctrlFolder . $ctrlFile)) {
            $url = "/__dev/open?path=" . urlencode($ctrlFolder . $ctrlFile);
            $response['issues'][] = "⚠️ Controller file already exists: <a href='$url' target='_blank'>$ctrlFile</a>";
        }
    }

    // Clean any output and send JSON response
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;

} catch (Throwable $e) {
    // Clean any output and send JSON error response
    ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'issues' => ['⚠️ An error occurred during validation: ' . $e->getMessage()]
    ]);
    exit;
}
