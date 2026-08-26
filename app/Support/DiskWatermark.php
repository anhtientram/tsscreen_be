<?php

namespace App\Support;

final class DiskWatermark
{
    public static function uploadsRoot(): string
    {
        $root = (string) (config('filesystems.disks.uploads.root') ?: public_path());
        $uploads = rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'uploads';
        if (is_dir($uploads)) {
            return $uploads;
        }

        return is_dir($root) ? $root : storage_path('app');
    }

    public static function freeBytes(?string $path = null): ?int
    {
        $free = @disk_free_space($path ?? self::uploadsRoot());
        if ($free === false) {
            return null;
        }

        return (int) $free;
    }

    /**
     * Ổ gần đầy theo % — chỉ dùng khi biết total >= 4GB (disk thật).
     */
    public static function usedRatio(?string $path = null): float
    {
        $path ??= self::uploadsRoot();
        $free = @disk_free_space($path);
        $total = @disk_total_space($path);

        if (! $total || $free === false) {
            return 0.0;
        }

        return ($total - $free) / $total;
    }

    public static function isFull(float $threshold = 0.85): bool
    {
        return ! self::canWrite(1, $threshold);
    }

    /**
     * Còn đủ chỗ ghi $bytes trên volume uploads, luôn chừa reserve.
     * Mọi hosting (không chỉ Wasmer). Testing: luôn cho phép (Storage::fake).
     */
    public static function canWrite(int $bytes, float $ratioThreshold = 0.85): bool
    {
        if (app()->environment('testing')) {
            return true;
        }

        $bytes = max(0, $bytes);
        $reserve = (int) config('filesystems.uploads_reserve_bytes', 64 * 1024 * 1024);
        $need = $bytes + max(0, $reserve);

        $path = self::uploadsRoot();
        $free = self::freeBytes($path);
        if ($free !== null && $free < $need) {
            return false;
        }

        $total = @disk_total_space($path);
        // Disk thật đủ lớn: chặn khi used >= 85%. Disk ảo/Wasmer nhỏ: chỉ tin free bytes ở trên.
        if ($total && $total >= 4 * 1024 * 1024 * 1024 && self::usedRatio($path) >= $ratioThreshold) {
            return false;
        }

        return true;
    }
}
