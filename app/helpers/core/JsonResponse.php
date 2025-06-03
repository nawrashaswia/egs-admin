<?php

namespace App\Helpers\Core;

/**
 * Utility to return JSON responses and terminate
 */
class JsonResponse
{
    /**
     * Send a generic JSON response and exit
     *
     * @param mixed $data
     * @param int $status HTTP status code
     */
    
    
    
     public static function send(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Shortcut for sending a success response
     *
     * @param mixed $payload
     */
    public static function success(mixed $payload = null): void
    {
        self::send([
            'status' => 'success',
            'data'   => $payload,
        ]);
    }

    /**
     * Shortcut for sending an error response
     *
     * @param string $message
     * @param int $status
     */
    public static function error(string $message, int $status = 400): void
    {
        self::send([
            'status'  => 'error',
            'message' => $message,
        ], $status);
    }
}
