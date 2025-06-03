<?php

namespace App\Core;

/**
 * Simple application service container
 */
class App
{
    /**
     * @var array<string, mixed>
     */
    private static array $services = [];

    public static function set(string $key, mixed $instance): void
    {
        self::$services[$key] = $instance;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$services[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset(self::$services[$key]);
    }

    public static function forget(string $key): void
    {
        unset(self::$services[$key]);
    }

    public static function all(): array
    {
        return self::$services;
    }

    /**
     * Resolve service or lazily create if closure passed
     */
    public static function resolve(string $key, mixed $default = null): mixed
    {
        if (self::has($key)) {
            return self::get($key);
        }

        if ($default instanceof \Closure) {
            $resolved = $default();
            self::set($key, $resolved);
            return $resolved;
        }

        return $default;
    }
}
