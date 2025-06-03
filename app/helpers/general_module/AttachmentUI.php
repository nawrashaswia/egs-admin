<?php

namespace App\Helpers\General_Module;

use App\Core\DB;
use App\Helpers\Core\HiLoSequence;
use Exception;

class AttachmentUI
{
    private static ?string $cachedModule = null;

    /**
     * Render the attachment UI block with modal support.
     *
     * @param string|null $referenceNumber Optional reference number; auto-generated if not provided.
     * @param int|null $ruleId Optional rule ID for validation (null = use default rule).
     */
    public static function render(?string $referenceNumber = null, ?int $ruleId = null): void
    {
        if (!$referenceNumber) {
            $referenceNumber = $GLOBALS['ref'] ?? null;
            if (!$referenceNumber) {
                throw new Exception("Missing reference number. Ensure HiLoSequence is initialized in the header.");
            }
        }

        $module = self::detectModuleName();
        $ref = $referenceNumber;

        // ✅ Expose to frontend
        echo "<script>
            window.currentAttachmentRef = " . json_encode($ref) . ";
            window.currentModuleName = " . json_encode($module) . ";
            window.currentAttachmentRuleId = " . json_encode($ruleId) . ";
        </script>";

        require APP_PATH . '/helpers/general_module/attachment_block.php';
    }

    private static function detectModuleName(): string
    {
        if (self::$cachedModule !== null) {
            return self::$cachedModule;
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        foreach ($backtrace as $trace) {
            if (!empty($trace['file'])) {
                $normalized = str_replace('\\', '/', $trace['file']);
                if (str_contains($normalized, '/app/modules/')) {
                    $parts = explode('/', $normalized);
                    $i = array_search('modules', $parts);
                    if ($i !== false && isset($parts[$i + 1])) {
                        return self::$cachedModule = strtolower($parts[$i + 1]);
                    }
                }
            }
        }

        return self::$cachedModule = 'unknown';
    }
}
