<?php

namespace App\Helpers\general_module\logmanager;

class LogContextBuilder
{
    public static function fromRequest(): array
    {
        return [
            'url'    => $_SERVER['REQUEST_URI'] ?? 'n/a',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'n/a',
            'user'   => $_SESSION['user_name'] ?? 'guest',
            'ip'     => $_SERVER['REMOTE_ADDR'] ?? 'n/a',
            'agent'  => $_SERVER['HTTP_USER_AGENT'] ?? 'n/a'
        ];
    }

    public static function enrich(array $base = []): array
    {
        return array_merge(self::fromRequest(), $base);
    }
}
