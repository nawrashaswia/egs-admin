<?php

namespace App\Helpers\Core;

use Exception;

/**
 * Parses legacy routes.php into routes.map.php structure
 * Used by Routes Builder interface
 */
class RouteMigratorHelper
{
    /**
     * Parses an old routes.php file into a standardized structure
     *
     * @param string $moduleName
     * @return array ['views' => [], 'controllers' => []]
     * @throws Exception if file not found
     */
    public static function parseRoutes(string $moduleName): array
    {
        $basePath = defined('MODULES_PATH')
            ? MODULES_PATH . '/' . $moduleName . '/controllers'
            : realpath(__DIR__ . '/../../modules/' . $moduleName . '/controllers');

        $routeFile = $basePath . '/routes.php';

        if (!file_exists($routeFile)) {
            throw new Exception("❌ Routes file not found for module: $moduleName");
        }

        $routes = [
            'views' => [],
            'controllers' => []
        ];

        $fileContent = file_get_contents($routeFile);

        // View routes: RouteHelper::view('/path', 'view_name');
        preg_match_all(
            '/RouteHelper::view\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*(\[[^\)]*\]))?\s*\)/',
            $fileContent,
            $viewMatches,
            PREG_SET_ORDER
        );
        foreach ($viewMatches as $match) {
            $routes['views'][$match[1]] = $match[2];
        }

        // Controller routes with require_once
        preg_match_all(
            '/RouteHelper::(get|post)\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*function\s*\(\)\s*{[^}]*require_once\s+[\'"]([^\'"]+)[\'"];/s',
            $fileContent,
            $ctrlMatches,
            PREG_SET_ORDER
        );
        foreach ($ctrlMatches as $match) {
            $routes['controllers'][] = [
                'method' => strtolower($match[1]),
                'path'   => $match[2],
                'file'   => basename($match[3]) // just filename
            ];
        }

        return $routes;
    }
}
