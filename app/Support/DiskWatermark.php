<?php

namespace App\Support;

final class DiskWatermark
{
    public static function usedRatio(?string $path = null): float
    {
        $path ??= storage_path('app');
        $free = @disk_free_space($path);
        $total = @disk_total_space($path);

        if (! $total || $free === false) {
            return 0.0;
        }

        return ($total - $free) / $total;
    }

    public static function isFull(float $threshold = 0.85): bool
    {
        if (app()->environment('testing')) {
            return false;
        }

        return self::usedRatio() >= $threshold;
    }
}
