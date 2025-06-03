<?php

namespace App\Core;

use App\Core\DB;
use App\Core\TraceManager;
use App\Core\LogFormatter;
use App\Helpers\General_Module\LogManager\LogContextBuilder;

class Logger
{
    /** Used to avoid logging the same message repeatedly */
    private static array $recentLogCache = [];

    /** Duplicate window in seconds */
    private static int $logRepeatWindow = 30;

    /** Avoid recursive logs */
    private static bool $isWritingLog = false;

    /**
     * 🔔 Main logging method: creates structured log entries.
     */
    public static function trigger(
        string $event,
        array $context = [],
        string $level = 'INFO',
        string $mode = 'system',
        string $tone = 'casual',
        string $verbosity = 'verbose'
    ): void {
        if (self::$isWritingLog) return;
        self::$isWritingLog = true;

        $traceMode = config('trace_mode', false);
        $traceId = TraceManager::getTraceId();

        // 🧠 Automatically enrich context (file, url, method, etc.)
        $context = LogContextBuilder::enrich($context);

        // 🗣️ Format with tone and verbosity
        $data = LogFormatter::formatConversational($event, $context, $level, $mode, $tone, $verbosity);

        // 🧼 Skip if log was already written recently
        if (self::isDuplicate($event, $context)) {
            self::$isWritingLog = false;
            return;
        }

        // 🪓 Skip if log has no real value
        if (!self::isUseful($data)) {
            self::$isWritingLog = false;
            return;
        }

        try {
            if ($traceMode && $traceId && $mode === 'trace') {
                self::storeConstructionLog($data);
            } else {
                self::storeLog($data);
            }
        } catch (\Throwable $e) {
            $data['event'] .= ' [Logged to emergency file: DB failure]';
            $line = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            file_put_contents(LOGS_PATH . '/emergency_log.json', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        }

        self::$isWritingLog = false;
    }

    /**
     * 🔁 Prevents repeated logs from spamming the system.
     */
private static function isDuplicate(string $event, array $context): bool
{
    // Use only stable keys that don't change between requests
    $stableKeys = ['file', 'module', 'action', 'source', 'dsn', 'tag'];
    $filteredContext = array_intersect_key($context, array_flip($stableKeys));

    $hash = md5($event . json_encode($filteredContext));
    $now = time();

    // Check if same log occurred within repeat window
    if (isset(self::$recentLogCache[$hash]) && ($now - self::$recentLogCache[$hash]) < self::$logRepeatWindow) {
        return true;
    }

    // Store this hash for future comparison
    self::$recentLogCache[$hash] = $now;

    // Clean expired ones
    foreach (self::$recentLogCache as $h => $t) {
        if (($now - $t) > self::$logRepeatWindow * 2) {
            unset(self::$recentLogCache[$h]);
        }
    }

    return false;
}

    /**
     * 🧠 Decides whether this log is worth saving.
     */
    private static function isUseful(array $data): bool
    {
        // Always keep critical-level logs
        if (in_array($data['level'], ['ERROR', 'CRITICAL', 'ALERT'])) {
            return true;
        }

        // Ignore if it's too short and vague
        if (strlen(trim($data['event'])) < 10) {
            return false;
        }

        // Keep if helpful context exists
        $context = $data['context'] ?? [];
        return isset($context['file']) || isset($context['module']) || isset($context['action']) || isset($context['trace']) || isset($context['tag']);
    }

    /**
     * 🧾 Stores a normal log in the `logs` table.
     */
    private static function storeLog(array $entry): void
    {
        if (isset($entry['context']) && !is_string($entry['context'])) {
            $entry['context'] = json_encode($entry['context'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        // --- Extract tag from context and store in tag column ---
        $tag = null;
        if (!empty($entry['context'])) {
            $ctx = json_decode($entry['context'], true);
            if (is_array($ctx) && isset($ctx['tag'])) {
                $tag = $ctx['tag'];
            }
        }
        $entry['tag'] = $tag;
        // --- DB deduplication for logs with a tag ---
        if ($tag) {
            $pdo = \App\Core\DB::connect();
            $sql = "SELECT COUNT(*) FROM logs WHERE event = ? AND tag = ? AND timestamp >= ?";
            $since = date('Y-m-d H:i:s', time() - 30);
            // Debug output
            error_log('[DEDUP] event=' . $entry['event'] . ' tag=' . $tag . ' sql=' . $sql . ' params=' . json_encode([$entry['event'], $tag, $since]));
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$entry['event'], $tag, $since]);
            $count = $stmt->fetchColumn();
            error_log('[DEDUP] count=' . $count);
            if ($count > 0) {
                error_log('[DEDUP] Duplicate found, skipping insert');
                return;
            }
        }
        // --- End DB deduplication ---

        DB::insert('logs', $entry);
    }

    /**
     * 🏗️ Stores trace logs in both DB and JSON file (for debug sessions).
     */
    private static function storeConstructionLog(array $entry): void
    {
        if (isset($entry['context']) && !is_string($entry['context'])) {
            $entry['context'] = json_encode($entry['context'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        DB::insert('construction_logs', $entry);

        $path = TraceManager::getTraceLogPath($entry['trace_id']);
        $existing = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
        $existing[] = $entry;

        file_put_contents($path, json_encode($existing, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * 🕵️ Shortcut for auditing specific user/system events.
     */
    public static function audit(string $event, array $context = [], string $user = null): void
    {
        $context['audit'] = true;
        $context['user'] = $user ?? ($_SESSION['user_name'] ?? 'system');
        self::trigger($event, $context, 'AUDIT', 'audit');
    }

    /**
     * 🚧 Enables manual trace session (debug-focused mode).
     */
    public static function startConstructionTrace(string $file = 'unknown', string $notes = ''): void
    {
        if (!config('trace_mode')) return;

        if (isset($_SESSION['trace_file']) && realpath($file) !== $_SESSION['trace_file']) {
            TraceManager::endTrace();
        }

        TraceManager::startTrace($file, $notes);
    }
}
