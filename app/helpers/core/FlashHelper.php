<?php

namespace App\Helpers\Core;

/**
 * Flash message helper — supports toast/alert rendering and multiple keys
 */
class FlashHelper
{
    private static bool $consumed = false;

    /**
     * Set a flash value by key and optional type
     */
    public static function set(string $key, mixed $value, string $type = 'info'): void
    {
        if (!session_id()) session_start();
        $_SESSION['__flash'][$key] = [
            'type' => $type,
            'value' => $value
        ];
        self::$consumed = false;
    }

    /**
     * Get a flash value and consume it
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!session_id()) session_start();
        if (!isset($_SESSION['__flash'][$key])) return $default;

        $value = $_SESSION['__flash'][$key]['value'] ?? $default;
        unset($_SESSION['__flash'][$key]);
        return $value;
    }

    /**
     * Check if a flash key exists
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION['__flash'][$key]);
    }

    /**
     * Render a Bootstrap toast for any flash key with known type
     */
    public static function renderToast(string $key = null): void
    {
        if (!session_id()) session_start();
        if (!isset($_SESSION['__flash']) || empty($_SESSION['__flash'])) return;

        $items = $key ? [ $key => $_SESSION['__flash'][$key] ?? null ] : $_SESSION['__flash'];

        foreach ($items as $flashKey => $entry) {
            if (!$entry) continue;

            $type = $entry['type'] ?? 'info';
            $text = $entry['value'] ?? '';
            $icon = match ($type) {
                'success' => 'check',
                'error'   => 'alert-triangle',
                'warning' => 'alert-circle',
                default   => 'info-circle',
            };
            $colorClass = match ($type) {
                'success' => 'bg-success text-white',
                'error'   => 'bg-danger text-white',
                'warning' => 'bg-warning text-dark',
                default   => 'bg-info text-white',
            };

            echo "
            <div class='position-fixed top-0 end-0 p-3' style='z-index:1055;min-width:320px;max-width:400px;'>
              <div class='toast $colorClass border-0 shadow-sm w-100' role='alert' aria-live='assertive' aria-atomic='true'
                   data-bs-autohide='true' data-bs-delay='4000'>
                <div class='d-flex justify-content-between align-items-center px-3 py-2'>
                  <div class='d-flex align-items-center'>
                    <i class='ti ti-$icon me-2'></i>
                    <span>$text</span>
                  </div>
                  <button type='button' class='btn-close btn-close-white ms-3' data-bs-dismiss='toast' aria-label='Close'></button>
                </div>
              </div>
            </div>
            ";
        }

        echo "
        <script>
          document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.toast').forEach(toast => {
              new bootstrap.Toast(toast).show();
            });
          });
        </script>
        ";

        self::consume();
    }

    /**
     * Render a simple fixed-position alert (optional use)
     */
    public static function show(string $key = 'default'): void
    {
        if (!session_id()) session_start();
        if (!isset($_SESSION['__flash'][$key]) || self::$consumed) return;

        $type = $_SESSION['__flash'][$key]['type'] ?? 'info';
        $text = $_SESSION['__flash'][$key]['value'] ?? '';
        $icon = match ($type) {
            'success' => 'check',
            'error'   => 'alert-triangle',
            'warning' => 'alert-circle',
            default   => 'info-circle',
        };

        echo "
        <div class='alert alert-$type alert-dismissible fade show fixed-top m-3 shadow-sm'
             style='z-index:9999; max-width:360px; right:0; left:auto;' role='alert'>
            <div class='d-flex'>
                <div><i class='ti ti-$icon me-2'></i></div>
                <div>$text</div>
            </div>
            <a class='btn-close' data-bs-dismiss='alert' aria-label='Close'></a>
        </div>
        ";

        self::consume($key);
    }

    /**
     * Remove consumed flash messages
     */
    private static function consume(string $key = null): void
    {
        self::$consumed = true;
        if (!session_id()) session_start();

        if ($key && isset($_SESSION['__flash'][$key])) {
            unset($_SESSION['__flash'][$key]);
        } else {
            unset($_SESSION['__flash']);
        }
    }
}
