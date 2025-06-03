<?php

namespace App\Helpers\Core;

class PermissionScannerHelper
{
    protected string $baseModulesPath = __DIR__ . '/../../modules';

    public function scanAll(): array
    {
        $permissions = [];

        foreach (glob($this->baseModulesPath . '/*_module') as $modulePath) {
            $module = basename($modulePath, '_module');

            // Scan views
            $viewPath = "$modulePath/views";
            if (is_dir($viewPath)) {
                $permissions = array_merge($permissions, $this->scanViews($module, $viewPath));
            }

            // Scan controllers
            $controllerPath = "$modulePath/controllers";
            if (is_dir($controllerPath)) {
                $permissions = array_merge($permissions, $this->scanControllers($module, $controllerPath));
                $permissions = array_merge($permissions, $this->scanRouteMaps($module, $controllerPath));
            }
        }

        return $permissions;
    }

    protected function scanViews(string $module, string $path): array
    {
        $results = [];
        $files = $this->getPhpFiles($path);

        foreach ($files as $file) {
            $relative = $this->getRelativePath($file);
            $target = str_replace('/', '.', preg_replace('/\.php$/', '', $relative));

            $results[] = [
                'module' => $module,
                'type' => 'view',
                'target' => $target,
                'permission_key' => "$module.view.$target",
                'file' => basename($file),
                'source_file_path' => $relative,
                'is_auto_generated' => 1,
                'is_active' => 1,
                'description' => null,
            ];
        }

        return $results;
    }

    protected function scanControllers(string $module, string $path): array
    {
        $results = [];
        $files = $this->getPhpFiles($path);

        foreach ($files as $file) {
            $relative = $this->getRelativePath($file);
            $target = str_replace('/', '.', preg_replace('/\.php$/', '', $relative));

            $results[] = [
                'module' => $module,
                'type' => 'controller',
                'target' => $target,
                'permission_key' => "$module.controller.$target",
                'file' => basename($file),
                'source_file_path' => $relative,
                'is_auto_generated' => 1,
                'is_active' => 1,
                'description' => null,
            ];
        }

        return $results;
    }

    protected function scanRouteMaps(string $module, string $controllerPath): array
    {
        $results = [];
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($controllerPath));

        foreach ($rii as $file) {
            if ($file->isFile() && $file->getFilename() === 'routes.map.php') {
                $lines = file($file->getPathname());

                foreach ($lines as $line) {
                    if (preg_match("/\\$router->(get|post|put|delete)\\s*\\(.*?,\\s*'([^']+\\.php)'\\)/", $line, $matches)) {
                        $handler = $matches[2];
                        $normalized = str_replace('/', '.', preg_replace('/\.php$/', '', $handler));

                        $results[] = [
                            'module' => $module,
                            'type' => 'controller',
                            'target' => $normalized,
                            'permission_key' => "$module.controller.$normalized",
                            'file' => basename($file->getPathname()),
                            'source_file_path' => $this->getRelativePath($file->getPathname()),
                            'is_auto_generated' => 1,
                            'is_active' => 1,
                            'description' => 'Auto-generated from route map',
                        ];
                    }
                }
            }
        }

        return $results;
    }

    protected function getPhpFiles(string $dir): array
    {
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $files = [];

        foreach ($rii as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = str_replace('\\', '/', $file->getPathname());
            }
        }

        return $files;
    }

    protected function getRelativePath(string $full): string
    {
        $full = realpath($full);
        $base = realpath(BASE_PATH);
        return ltrim(str_replace([$base, '\\'], ['', '/'], $full), '/');
    }
}
