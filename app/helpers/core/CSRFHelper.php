<?php
namespace App\Helpers\Core;

class CSRFHelper {
    public static function generateToken($form = 'default') {
        if (empty($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][$form] = [
            'token' => $token,
            'time' => time()
        ];
        return $token;
    }

    public static function getToken($form = 'default') {
        if (!isset($_SESSION['csrf_tokens'][$form])) {
            return self::generateToken($form);
        }
        return $_SESSION['csrf_tokens'][$form]['token'];
    }

    public static function validateToken($token, $form = 'default', $expirySeconds = 1800) {
        if (!isset($_SESSION['csrf_tokens'][$form])) return false;

        $entry = $_SESSION['csrf_tokens'][$form];
        $isValid = hash_equals($entry['token'], $token);
        $isFresh = (time() - $entry['time']) <= $expirySeconds;

        if ($isValid && $isFresh) {
            unset($_SESSION['csrf_tokens'][$form]); // one-time use
            return true;
        }

        return false;
    }

    public static function input($form = 'default', $fieldName = 'csrf_token') {
        $token = self::getToken($form);
        return '<input type="hidden" name="' . htmlspecialchars($fieldName, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '" value="' . htmlspecialchars($token, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '">';
    }
}
