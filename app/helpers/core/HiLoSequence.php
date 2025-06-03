<?php

namespace App\Helpers\Core;
require_once APP_PATH . '/helpers/core/ModuleDiscoveryHelper.php';

use App\Core\App;
use App\Helpers\Core\ModuleDiscoveryHelper;
use PDO;
use Exception;
use RuntimeException;

/**
 * HiLo-based unique reference number generator
 */
class HiLoSequence
{
    private const BLOCK_SIZE = 100;
    private const PADDING_LENGTH = 5;
    private static array $counters = [];

    public static function get(): string
    {
        if (!session_id()) session_start();

        $moduleName = self::detectModuleName();
        $key = $moduleName;

        if (!isset(self::$counters[$key])) {
            self::initBlock($key);
        }

        return self::generate($key);
    }

    public static function reset(): void
    {
        if (!session_id()) session_start();

        $moduleName = self::detectModuleName();
        unset(self::$counters[$moduleName]);
    }

    private static function generate(string $key): string
    {
        $counter = &self::$counters[$key];

        if ($counter['lo'] >= $counter['block_size']) {
            self::advanceBlock($key, $counter);
        }

        $ref = strtoupper(sprintf(
            '%s%s%s',
            $counter['prefix'],
            self::hiToAlpha($counter['hi']),
            str_pad($counter['lo'], self::PADDING_LENGTH, '0', STR_PAD_LEFT)
        ));

        $counter['lo']++;

        try {
            $stmt = App::get('db')->prepare("UPDATE sequence_tracker SET lo_cursor = ? WHERE module_name = ?");
            $stmt->execute([$counter['lo'], $key]);
        } catch (Exception $e) {
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                error_log("[HiLo] Warning: Failed to persist LO cursor: " . $e->getMessage());
            }
        }

        return $ref;
    }

    private static function initBlock(string $key): void
    {
        $pdo = App::get('db');

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT * FROM sequence_tracker WHERE module_name = ? FOR UPDATE");
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new RuntimeException("HiLoSequence: Module '{$key}' not found in sequence_tracker.");
            }

            $hi = (int) $row['last_hi_value'];
            $lo = (int) $row['lo_cursor'] ?? 0;
            $blockSize = (int) ($row['block_size'] ?? self::BLOCK_SIZE);
            $prefix = $row['prefix'] ?? self::sanitizePrefix($key);

            $pdo->commit();

            self::$counters[$key] = [
                'hi' => $hi,
                'lo' => $lo,
                'block_size' => $blockSize,
                'prefix' => $prefix,
            ];

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new RuntimeException("Failed to initialize HiLo block for '{$key}'", 0, $e);
        }
    }

    private static function advanceBlock(string $key, array &$counter): void
    {
        $pdo = App::get('db');
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("UPDATE sequence_tracker 
                SET last_hi_value = last_hi_value + 1, updated_at = NOW()
                WHERE module_name = ?");
            $stmt->execute([$key]);

            $stmt = $pdo->prepare("SELECT last_hi_value FROM sequence_tracker WHERE module_name = ?");
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $pdo->commit();

            $counter['hi'] = (int) $row['last_hi_value'];
            $counter['lo'] = 0;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw new RuntimeException("Failed to advance HiLo block for '{$key}'", 0, $e);
        }
    }

    private static function hiToAlpha(int $index): string
    {
        $result = '';
        while ($index >= 0) {
            $remainder = $index % 26;
            $result = chr(65 + $remainder) . $result;
            $index = intdiv($index, 26) - 1;
        }
        return $result;
    }

    private static function sanitizePrefix(string $name): string
    {
        return strtoupper(substr(preg_replace('/[^A-Z]/i', '', $name), 0, 3));
    }

    public static function detectModuleName(): string
    {
        $allModules = ModuleDiscoveryHelper::getAllModules();

        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10) as $item) {
            if (!empty($item['file'])) {
                $path = str_replace('\\', '/', $item['file']);
                if (str_contains($path, '/app/modules/')) {
                    $parts = explode('/', $path);
                    $i = array_search('modules', $parts);
                    if ($i !== false && isset($parts[$i + 1])) {
                        $module = strtolower($parts[$i + 1]);
                        if (in_array($module, $allModules)) {
                            return $module;
                        }
                    }
                }
            }
        }

        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            echo "<pre style='color:red;'>HiLoSequence Debug: Could not detect a valid module name from call stack.</pre>";
        }

        throw new RuntimeException(
            DEBUG_MODE
                ? "HiLoSequence Debug: Could not detect a valid module name from call stack."
                : "HiLoSequence: Module name could not be detected."
        );
    }
}
