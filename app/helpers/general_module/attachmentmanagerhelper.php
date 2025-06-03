<?php

namespace App\Helpers\General_Module;

use App\Core\DB;
use PDO;
use Throwable;

class AttachmentManagerHelper
{
    public static function detectModule(string $ref): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        foreach ($trace as $entry) {
            if (!empty($entry['file'])) {
                $normalized = str_replace('\\', '/', $entry['file']);
                if (str_contains($normalized, '/app/modules/')) {
                    $parts = explode('/', $normalized);
                    $i = array_search('modules', $parts);
                    if ($i !== false && isset($parts[$i + 1])) {
                        return strtolower($parts[$i + 1]);
                    }
                }
            }
        }
        return 'unknown';
    }

    public static function getUploadPath(string $ref, string $module): string
    {
        $base = PUBLIC_PATH . "/uploads/attachments";
        $target = "$base/$module/$ref";

        if (!@mkdir($target, 0777, true) && !is_dir($target)) {
            $fallback = "$base/error_uploads/$ref";
            @mkdir($fallback, 0777, true);
            return $fallback;
        }

        return $target;
    }

    public static function getValidationRules(?int $ruleId = null): array
    {
        $pdo = DB::connect();

        if ($ruleId) {
            $stmt = $pdo->prepare("SELECT * FROM attachment_rules WHERE id = ? AND is_active = 1");
            $stmt->execute([$ruleId]);
        } else {
            $stmt = $pdo->query("SELECT * FROM attachment_rules WHERE is_active = 1 ORDER BY id ASC LIMIT 1");
        }

        $rule = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'extensions' => explode(',', strtolower($rule['allowed_extensions'] ?? '*')),
            'max_size_mb' => (int)($rule['max_size_mb'] ?? 25)
        ];
    }

    public static function isExtensionAllowed(string $ext, array $allowed): bool
    {
        return $allowed === ['*'] || in_array(strtolower($ext), $allowed);
    }

    public static function isDuplicate(string $ref, string $filename): bool
    {
        $pdo = DB::connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM attachments WHERE reference_number = ? AND file_name = ? AND is_deleted = 0");
        $stmt->execute([$ref, $filename]);
        return $stmt->fetchColumn() > 0;
    }

    public static function saveFile(string $ref, string $filename, array $file, ?int $ruleId = null): array
    {
        self::logError("📦 Starting file save process for ref=$ref, filename=$filename");
        
        $pdo = DB::connect();
        $module = self::detectModule($ref);
        self::logError("📦 Detected module: $module");
        
        $rules = self::getValidationRules($ruleId);
        self::logError("📦 Validation rules:", $rules);

        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            self::logError("❌ Invalid file upload - tmp_name not set or not uploaded");
            return ['success' => false, 'message' => "Invalid uploaded file."];
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $size = $file['size'] ?? 0;

        self::logError("📦 File details: size=$size bytes, ext=.$ext");

        if (!self::isExtensionAllowed($ext, $rules['extensions'])) {
            self::logError("❌ Extension not allowed: .$ext");
            return ['success' => false, 'message' => "Extension .$ext is not allowed."];
        }

        if ($size > $rules['max_size_mb'] * 1024 * 1024) {
            self::logError("❌ File too large: $size bytes > {$rules['max_size_mb']}MB");
            return [
                'success' => false,
                'message' => "File is {$size} bytes, exceeds max size of {$rules['max_size_mb']}MB."
            ];
        }

        $folder = self::getUploadPath($ref, $module);
        self::logError("📦 Upload path: $folder");
        
        $targetPath = "$folder/$filename";
        self::logError("📦 Target path: $targetPath");

        if (file_exists($targetPath)) {
            self::logError("❌ File already exists at target path");
            return ['success' => false, 'message' => "A file with this name already exists."];
        }

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            self::logError("❌ Failed to move uploaded file. PHP error: " . error_get_last()['message']);
            return ['success' => false, 'message' => "Failed to move uploaded file."];
        }

        self::logError("📦 File moved successfully, saving to database");

        $uploadedBy = $_SESSION['user_id'] ?? 0;
        try {
            $stmt = $pdo->prepare("INSERT INTO attachments 
                (module, reference_number, file_name, uploaded_by, uploaded_at, is_deleted)
                VALUES (?, ?, ?, ?, NOW(), 0)");
            $stmt->execute([$module, $ref, $filename, $uploadedBy]);
            self::logError("✅ File saved successfully to database");
        } catch (Throwable $e) {
            self::logError("❌ Database error: " . $e->getMessage());
            // Try to clean up the uploaded file
            @unlink($targetPath);
            return ['success' => false, 'message' => "Database error: " . $e->getMessage()];
        }

        return ['success' => true, 'message' => "File uploaded successfully."];
    }

    public static function logError(string $msg): void
    {
        $logsDir = dirname(__DIR__, 3) . '/logs';
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0777, true);
        }
        $logPath = $logsDir . '/attachment_debug.log';
        @file_put_contents($logPath, date('Y-m-d H:i:s') . " - $msg\n", FILE_APPEND);
    }
}
