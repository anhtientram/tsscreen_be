<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

final class TvBoxApkStorage
{
    public const DISK = 'releases';

    public const FILENAME = 'tvbox.apk';

    public static function directory(): string
    {
        return Storage::disk(self::DISK)->path('');
    }

    public static function absolutePath(): string
    {
        return Storage::disk(self::DISK)->path(self::FILENAME);
    }

    public static function exists(): bool
    {
        return Storage::disk(self::DISK)->exists(self::FILENAME);
    }

    public static function sizeBytes(): ?int
    {
        if (! self::exists()) {
            return null;
        }

        $size = filesize(self::absolutePath());

        return $size === false ? null : $size;
    }

    public static function formattedSize(): string
    {
        $bytes = self::sizeBytes();

        if ($bytes === null) {
            return '—';
        }

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 1, ',', '.').' MB';
        }

        return number_format($bytes / 1024, 0, ',', '.').' KB';
    }

    public static function ensureDirectory(): void
    {
        File::ensureDirectoryExists(self::directory());
    }

    public static function store(UploadedFile $file): string
    {
        return self::storeFromPath($file->getRealPath());
    }

    public static function storeFromPath(string $sourcePath): string
    {
        self::ensureDirectory();

        $target = self::absolutePath();
        if (! copy($sourcePath, $target)) {
            throw new \RuntimeException('Could not store TV Box APK.');
        }

        return self::publicUrl();
    }

    public static function publicUrl(?string $baseUrl = null): string
    {
        $base = rtrim($baseUrl ?: (string) config('app.url'), '/');

        return $base.'/apk/'.self::FILENAME;
    }
}
