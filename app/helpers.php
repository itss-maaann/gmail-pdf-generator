<?php

use Illuminate\Support\Facades\Log;

if (!function_exists('logExecutionStats')) {
    function logExecutionStats(string $label, float $startTime, bool $isError = false): void
    {
        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        if ($duration < 1) {
            $formattedDuration = round($duration * 1000) . ' ms';
        } elseif ($duration < 60) {
            $formattedDuration = round($duration, 2) . ' sec';
        } else {
            $minutes = floor($duration / 60);
            $seconds = round($duration % 60);
            $formattedDuration = "{$minutes} min {$seconds} sec";
        }

        // Memory usage
        $memory = memory_get_peak_usage(true); // in bytes
        $formattedMemory = number_format($memory / 1048576, 2) . ' MB';

        $message = "{$label} — Time: {$formattedDuration}, Memory: {$formattedMemory}";

        if ($isError) {
            Log::error($message);
        } else {
            Log::info($message);
        }
    }
}
