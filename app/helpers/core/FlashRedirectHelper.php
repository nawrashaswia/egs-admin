<?php

namespace App\Helpers\Core;

use App\Helpers\Core\FlashHelper;

/**
 * Flash + redirect helper
 */
class FlashRedirectHelper
{
    /**
     * Set success message and redirect
     *
     * @param string $message
     * @param string $redirectTo
     */
    public static function success(string $message, string $redirectTo): void
    {
        self::set('success', $message);
        self::go($redirectTo);
    }

    /**
     * Set error message and redirect
     *
     * @param string $message
     * @param string $redirectTo
     */
    public static function error(string $message, string $redirectTo): void
    {
        self::set('error', $message);
        self::go($redirectTo);
    }

    /**
     * Set info message and redirect
     */
    public static function info(string $message, string $redirectTo): void
    {
        self::set('info', $message);
        self::go($redirectTo);
    }

    /**
     * Set warning message and redirect
     */
    public static function warning(string $message, string $redirectTo): void
    {
        self::set('warning', $message);
        self::go($redirectTo);
    }

    /**
     * Internal flash setter
     */
    private static function set(string $type, string $text): void
    {
        FlashHelper::set($type, $text);
    }

    /**
     * Internal redirect function
     */
    private static function go(string $path): void
    {
        header("Location: " . BASE_URL . $path);
        exit;
    }
}
