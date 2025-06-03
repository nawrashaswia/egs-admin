<?php
namespace App\Helpers\Core;

use App\Core\DB;

class ExtensionHelper
{
    private static array $cache = [];

    public static function getExtensionMappings(): array
    {
        if (!empty(self::$cache)) {
            return self::$cache;
        }

        try {
            $pdo = DB::connect(); // or use App::get('db')->pdo();
            $stmt = $pdo->query("SELECT extension, label FROM extensions_reference WHERE active = 1");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return []; // or log via LogHelper
        }

        $map = [];
        foreach ($rows as $row) {
            $ext = strtolower($row['extension']);
            $label = $row['label'] ?? strtoupper($ext);
            $icon = match ($ext) {
                'sql' => 'ti-database',
                'zip', 'rar', '7z' => 'ti-archive',
                default => 'ti-file-type-' . $ext
            };
            $map[$ext] = ['label' => $label, 'icon' => $icon];
        }

        self::$cache = $map;
        return $map;
    }
}
