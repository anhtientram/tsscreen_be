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

        $path = storage_path('app');
        $free = @disk_free_space($path);
        $total = @disk_total_space($path);
        // Wasmer/WASM: disk ảo nhỏ, disk_total_space hay báo >85% dù vẫn ghi được
        if (! $total || $free === false || $total < 4 * 1024 * 1024 * 1024) {
            return false;
        }

        return (($total - $free) / $total) >= $threshold;
    }
}
