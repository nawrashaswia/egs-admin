<?php

namespace App\Helpers\Core;

class ValidationHelper
{
    public static function required(mixed $value): bool
    {
        return isset($value) && trim((string)$value) !== '';
    }

    public static function email(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function minLength(mixed $value, int $min): bool
    {
        return mb_strlen(trim((string)$value)) >= $min;
    }

    public static function maxLength(mixed $value, int $max): bool
    {
        return mb_strlen(trim((string)$value)) <= $max;
    }

    public static function match(mixed $value1, mixed $value2): bool
    {
        return $value1 === $value2;
    }

    public static function in(mixed $value, array $array): bool
    {
        return in_array($value, $array, true);
    }

    /**
     * Run a full set of validation rules on provided data
     *
     * @param array $rules e.g. ['email' => [['required'], ['email']]]
     * @param array $data  e.g. $_POST or merged input
     * @return array Errors by field (if any)
     */
    public static function errors(array $rules, array $data): array
    {
        $errors = [];

        foreach ($rules as $field => $checks) {
            foreach ($checks as $check) {
                $type = $check[0] ?? null;
                $params = array_slice($check, 1);
                $value = $data[$field] ?? null;

                $valid = match ($type) {
                    'required' => self::required($value),
                    'email'    => self::email($value),
                    'min'      => self::minLength($value, $params[0] ?? 0),
                    'max'      => self::maxLength($value, $params[0] ?? PHP_INT_MAX),
                    'match'    => self::match($value, $data[$params[0] ?? ''] ?? null),
                    'in'       => self::in($value, $params[0] ?? []),
                    default    => true,
                };

                if (!$valid) {
                    $errors[$field][] = $type;
                }
            }
        }

        return $errors;
    }
}
