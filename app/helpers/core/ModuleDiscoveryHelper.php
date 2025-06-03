<?php

namespace App\Helpers\Core;

/**
 * Discovers available modules from /app/modules/
 */
class ModuleDiscoveryHelper
{
    /**
     * Return a list of all module names (folder names)
     *
     * @param string|null $path Optional path override
     * @return array List of module names
     */
    public static function getAllModules(?string $path = null): array
    {
        $modulesPath = $path ?? (defined('MODULES_PATH')
            ? MODULES_PATH
            : realpath(__DIR__ . '/../../modules'));

        if (!is_dir($modulesPath)) {
            return [];
        }

        $dirs = array_filter(glob($modulesPath . '/*'), 'is_dir');

        return array_map('basename', $dirs);
    }
}
