<?php

namespace Modules\general_module\Controllers\logmanager;

use App\Helpers\general_module\logmanager\LogQueryHelper;
use App\Helpers\general_module\logmanager\ConstructionTraceScanner;

class LogManagerController
{
    /**
     * View normal system logs (not related to trace)
     */
    public static function index(): void
    {
        $appConfig = $GLOBALS['config']['app'] ?? require CONFIG_PATH . '/app.php';
        $traceMode = !empty($appConfig['trace_mode']); // used in view

        $filters = [
            'level' => $_GET['level'] ?? '',
            'user' => $_GET['user'] ?? '',
            'trace_id' => $_GET['trace_id'] ?? '',
        ];
        $logs = \App\Helpers\general_module\logmanager\LogQueryHelper::fetchGeneralLogs(50, $filters);

        require APP_PATH . '/modules/general_module/views/logmanager/LogManagerView.php';
    }

    /**
     * View construction trace logs and diagnostics
     */
    public static function construction(): void
    {
        $logs = LogQueryHelper::fetchConstructionLogs();

        $traceData = ConstructionTraceScanner::analyze();

        $traceMode          = $traceData['traceMode'] ?? false;
        $traceSessions      = $traceData['traceSessions'] ?? [];
        $declaredTraceFiles = $traceData['declaredTraceFiles'] ?? [];

        require APP_PATH . '/modules/general_module/views/logmanager/ConstructionTraceView.php';
    }

    /**
     * Delete all logs
     */
    public static function deleteAll(): void
    {
        // Simple DB delete all logs
        \App\Core\DB::connect()->exec('DELETE FROM logs');
        // Optionally, add a flash message or redirect
        header('Location: /general/logmanager?deleted=1');
        exit;
    }

    /**
     * Delete all construction logs
     */
    public static function deleteAllConstruction(): void
    {
        // Simple DB delete all construction logs
        \App\Core\DB::connect()->exec('DELETE FROM construction_logs');
        header('Location: /general/logmanager/construction?deleted=1');
        exit;
    }

    /**
     * AJAX: Fetch construction logs with pagination
     */
    public static function ajaxFetchConstructionLogs(): void
    {
        header('Content-Type: application/json');
        try {
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $limit = max(1, $limit);
            $offset = max(0, $offset);
            $pdo = \App\Core\DB::connect();
            $sql = "SELECT * FROM construction_logs ORDER BY timestamp DESC LIMIT $limit OFFSET $offset";
            $stmt = $pdo->query($sql);
            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo json_encode($logs);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}
