<?php

use App\Helpers\Core\ModuleDiscoveryHelper;
use App\Helpers\Core\FlashHelper;

if (!class_exists(ModuleDiscoveryHelper::class)) {
    require_once HELPERS_PATH . '/core/ModuleDiscoveryHelper.php';
}
if (!class_exists(FlashHelper::class)) {
    require_once HELPERS_PATH . '/core/FlashHelper.php';
}

$modules = ModuleDiscoveryHelper::getAllModules();
$log = $_SESSION['route_log'] ?? [];
unset($_SESSION['route_log']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Builder | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        .route-preview {
            background: var(--tblr-bg-surface);
            border: 1px solid var(--tblr-border-color);
            border-radius: var(--tblr-border-radius);
            padding: 1rem;
            font-family: var(--tblr-font-monospace);
            font-size: 0.875rem;
        }
        .log-box {
            background: var(--tblr-bg-surface);
            border: 1px solid var(--tblr-border-color);
            border-radius: var(--tblr-border-radius);
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        .log-box:last-child {
            margin-bottom: 0;
        }
        .controller-block {
            background: var(--tblr-bg-surface);
            border: 1px solid var(--tblr-border-color);
            border-radius: var(--tblr-border-radius);
            padding: 1rem;
            margin-bottom: 1rem;
            position: relative;
        }
        .controller-block:last-child {
            margin-bottom: 0;
        }
        .remove-controller {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
        }
        .validation-status {
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }
        .validation-status.valid {
            color: var(--tblr-success);
        }
        .validation-status.invalid {
            color: var(--tblr-danger);
        }
    </style>
</head>
<body class="bg-light">
    <div class="page">
        <div class="page-wrapper">
            <div class="container-xl">
                <!-- Page header -->
                <div class="page-header d-print-none">
                    <div class="row align-items-center">
                        <div class="col">
                            <h2 class="page-title">
                                <i class="ti ti-route me-2"></i>
                                Route Builder
                            </h2>
                            <div class="text-muted mt-1">
                                Create and manage module routes with a visual interface
                            </div>
                        </div>
                        <div class="col-auto ms-auto d-print-none">
                            <div class="btn-list">
                                <a href="<?= BASE_URL ?>/system" class="btn btn-outline-secondary">
                                    <i class="ti ti-arrow-left me-1"></i>
                                    Back to System
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page body -->
                <div class="page-body">
                    <?php if (!empty($log)): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="ti ti-list me-2"></i>
                                    Operation Log
                                </h3>
                            </div>
                            <div class="card-body">
                                <?php foreach ($log as $msg): ?>
                                    <div class="log-box">
                                        <?= htmlspecialchars($msg) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= BASE_URL ?>/system/router-manager/save" id="routeForm">
                        <input type="hidden" name="views_json" id="views_json">
                        <input type="hidden" name="controllers_json" id="controllers_json">

                        <div class="row">
                            <!-- Module Selection -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="ti ti-package me-2"></i>
                                            Module Configuration
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label required">Select Module</label>
                                            <select id="moduleSelect" name="module" class="form-select" required onchange="updatePrefix()">
                                                <option value="">-- Select a module --</option>
                                                <?php foreach ($modules as $mod): ?>
                                                    <option value="<?= htmlspecialchars($mod) ?>"><?= htmlspecialchars($mod) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Page Title</label>
                                            <input type="text" name="title" class="form-control" placeholder="Enter page title">
                                            <div class="form-hint">This will be used as the page title in the browser</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- View Configuration -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="ti ti-file me-2"></i>
                                            View Configuration
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">View File</label>
                                            <div class="input-group">
                                                <span class="input-group-text">views/</span>
                                                <input type="text" name="view_file" id="viewFileInput" class="form-control" placeholder="e.g. users_UI.php" disabled>
                                            </div>
                                            <div class="validation-status" id="viewFileStatus"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Route Path</label>
                                            <div class="input-group">
                                                <span class="input-group-text" id="routePrefix">/</span>
                                                <input type="text" name="view_path" id="viewPathInput" class="form-control" placeholder="e.g. users" disabled>
                                            </div>
                                            <div class="validation-status" id="viewPathStatus"></div>
                                        </div>
                                        <div id="viewRoutePreview" class="route-preview d-none mt-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Controller Configuration -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="ti ti-code me-2"></i>
                                    Controller Routes
                                </h3>
                            </div>
                            <div class="card-body">
                                <div id="controller-fields">
                                    <!-- JavaScript will append blocks here -->
                                </div>
                                <button type="button" class="btn btn-primary mt-3" onclick="addController()">
                                    <i class="ti ti-plus me-1"></i> Add Controller
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="card-footer text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="ti ti-device-floppy me-1"></i>
                                Save Route Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/router_builder_script.js"></script>
    <script>
        document.getElementById('routeForm').addEventListener('submit', function (event) {
            const module = document.getElementById('moduleSelect').value;
            const viewFile = document.getElementById('viewFileInput').value.trim();
            const viewPath = document.getElementById('viewPathInput').value.trim();
            const views = {};
            const controllers = [];

            if (viewFile && viewPath) {
                views[`/${module.replace('_module', '')}/${viewPath}`] = `modules/${module}/views/${viewFile}`;
            }

            document.querySelectorAll('.controller-block').forEach(block => {
                const file = block.querySelector('input[name="controller_files[]"]').value.trim();
                const path = block.querySelector('input[name="controller_paths[]"]').value.trim();
                const method = block.querySelector('select[name="controller_methods[]"]').value;

                if (!file || !path) {
                    block.querySelectorAll('input').forEach(input => input.classList.add('is-invalid'));
                    event.preventDefault();
                    return;
                }

                controllers.push({
                    method: method,
                    path: `/${module.replace('_module', '')}/${path}`,
                    file: file
                });
            });

            document.getElementById('views_json').value = JSON.stringify(views);
            document.getElementById('controllers_json').value = JSON.stringify(controllers);
        });
    </script>
    <?php FlashHelper::renderToast(); ?>
</body>
</html>
