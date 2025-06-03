<?php

namespace App\Helpers\Core;

class PerformanceMonitor
{
    private static array $timers = [];
    private static array $counters = [];
    private static array $memorySnapshots = [];
    private static array $loopCounters = [];
    private static array $warnings = [];
    private static float $startTime = 0.0;
    private static int $startMemory = 0;

    public static function __constructStatic()
    {
        self::init();
    }

    public static function init(): void
    {
        self::$startTime = microtime(true);
        self::$startMemory = memory_get_usage();
    }

    public static function startTimer(string $name): void
    {
        self::$timers[$name] = [
            'start' => microtime(true),
            'memory' => memory_get_usage()
        ];
    }

    public static function endTimer(string $name): array
    {
        if (!isset(self::$timers[$name])) {
            return ['error' => 'Timer not found'];
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        $duration = $endTime - self::$timers[$name]['start'];
        $memoryUsed = $endMemory - self::$timers[$name]['memory'];

        // Check for potential issues
        if ($duration > 1.0) { // More than 1 second
            self::$warnings[] = "Timer '$name' took {$duration}s to complete";
        }
        if ($memoryUsed > 5 * 1024 * 1024) { // More than 5MB
            self::$warnings[] = "Timer '$name' used " . round($memoryUsed / 1024 / 1024, 2) . "MB of memory";
        }

        return [
            'duration' => $duration,
            'memory_used' => $memoryUsed,
            'warnings' => self::$warnings
        ];
    }

    public static function trackLoop(string $name, int $maxIterations = 1000): void
    {
        if (!isset(self::$loopCounters[$name])) {
            self::$loopCounters[$name] = 0;
        }

        self::$loopCounters[$name]++;

        if (self::$loopCounters[$name] > $maxIterations) {
            self::$warnings[] = "Loop '$name' exceeded {$maxIterations} iterations";
            // Optionally throw an exception or log the issue
            throw new \RuntimeException("Potential infinite loop detected in '$name'");
        }
    }

    public static function takeMemorySnapshot(string $name): void
    {
        self::$memorySnapshots[$name] = [
            'memory' => memory_get_usage(),
            'peak' => memory_get_peak_usage(),
            'time' => microtime(true)
        ];
    }

    public static function getMemorySnapshot(string $name): ?array
    {
        return self::$memorySnapshots[$name] ?? null;
    }

    public static function incrementCounter(string $name, int $amount = 1): void
    {
        if (!isset(self::$counters[$name])) {
            self::$counters[$name] = 0;
        }
        self::$counters[$name] += $amount;
    }

    public static function getCounter(string $name): int
    {
        return self::$counters[$name] ?? 0;
    }

    public static function getWarnings(): array
    {
        return self::$warnings;
    }

    public static function getTopIncludedFiles($limit = 10): array
    {
        $files = get_included_files();
        $fileStats = [];
        foreach ($files as $file) {
            $fileStats[] = [
                'file' => $file,
                'size' => file_exists($file) ? filesize($file) : 0
            ];
        }
        usort($fileStats, fn($a, $b) => $b['size'] <=> $a['size']);
        return array_slice($fileStats, 0, $limit);
    }

    public static function getSummary(): array
    {
        // Ensure initialization
        if (self::$startTime === 0.0) {
            self::init();
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        return [
            'total_time' => $endTime - self::$startTime,
            'total_memory' => $endMemory - self::$startMemory,
            'peak_memory' => memory_get_peak_usage(),
            'warnings' => self::$warnings,
            'counters' => self::$counters,
            'loop_counts' => self::$loopCounters,
            'top_files' => self::getTopIncludedFiles(10),
        ];
    }

    public static function reset(): void
    {
        self::$timers = [];
        self::$counters = [];
        self::$memorySnapshots = [];
        self::$loopCounters = [];
        self::$warnings = [];
        self::init();
    }
}

// Initialize the static properties
PerformanceMonitor::__constructStatic(); 