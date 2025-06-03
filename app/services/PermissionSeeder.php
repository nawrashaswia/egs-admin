<?php

namespace App\Services;

use App\Helpers\Core\PermissionScannerHelper;
use App\Core\Logger;
use PDO;

class PermissionSeeder
{
    protected PDO $db;
    protected PermissionScannerHelper $scanner;
    protected string $logMode;

    public function __construct(PDO $db, string $logMode = 'silent')
    {
        $this->db = $db;
        $this->scanner = new PermissionScannerHelper();
        $this->logMode = $logMode; // 'silent' | 'cli' | 'log'
    }

    public function run(): array
    {
        $scanned = $this->scanner->scanAll();
        $inserted = [];

        if ($this->logMode === 'cli') {
            echo "🔍 Total scanned permissions: " . count($scanned) . "\n";
        } elseif ($this->logMode === 'log') {
            Logger::trigger('🔍 Permission scan completed', [
                'count' => count($scanned)
            ], 'INFO', 'system');
        }

        foreach ($scanned as $perm) {
            if (!$this->exists($perm['permission_key'])) {
                $this->insert($perm);
                $inserted[] = $perm['permission_key'];

                if ($this->logMode === 'cli') {
                    echo " + {$perm['permission_key']}\n";
                } elseif ($this->logMode === 'log') {
                    Logger::trigger('🆕 Permission added', [
                        'permission' => $perm['permission_key'],
                        'file' => $perm['file'],
                        'path' => $perm['source_file_path']
                    ], 'INFO', 'system');
                }
            }
        }

        if ($this->logMode === 'cli' && empty($inserted)) {
            echo "✅ No new permissions inserted.\n";
        }

        return $inserted;
    }

    protected function exists(string $permissionKey): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM permissions WHERE permission_key = ?");
        $stmt->execute([$permissionKey]);
        return $stmt->fetchColumn() > 0;
    }

    protected function insert(array $perm): void
    {
        $stmt = $this->db->prepare("INSERT INTO permissions (
            permission_key, module, type, target, file, source_file_path,
            is_auto_generated, is_active, description
        ) VALUES (
            :permission_key, :module, :type, :target, :file, :source_file_path,
            :is_auto_generated, :is_active, :description
        )");

        $stmt->execute([
            'permission_key'     => $perm['permission_key'],
            'module'             => $perm['module'],
            'type'               => $perm['type'],
            'target'             => $perm['target'],
            'file'               => $perm['file'],
            'source_file_path'   => $perm['source_file_path'],
            'is_auto_generated'  => $perm['is_auto_generated'],
            'is_active'          => $perm['is_active'],
            'description'        => $perm['description'],
        ]);
    }
}
