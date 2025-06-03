<?php

namespace App\Helpers\Core;

use App\Helpers\Core\FlashRedirectHelper;

/**
 * Simple alias for FlashRedirectHelper
 */
class RedirectHelper
{
    public static function success(string $message, string $to): void
    {
        FlashRedirectHelper::success($message, $to);
    }

    public static function error(string $message, string $to): void
    {
        FlashRedirectHelper::error($message, $to);
    }

    public static function info(string $message, string $to): void
    {
        FlashRedirectHelper::info($message, $to);
    }

    public static function warning(string $message, string $to): void
    {
        FlashRedirectHelper::warning($message, $to);
    }
}
