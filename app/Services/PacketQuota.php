<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Order;
use App\Models\ResourceFile;
use App\Support\DiskWatermark;

class PacketQuota
{
    public static function activeOrder(int|string $customerId): ?Order
    {
        return Order::query()
            ->with('packet')
            ->where('customer_id', $customerId)
            ->where('pay', '1')
            ->where(function ($q): void {
                $q->whereNull('deleted')->orWhere('deleted', '!=', 'y');
            })
            ->orderByDesc('paid_id')
            ->get()
            ->first(fn (Order $order) => $order->isActiveNow());
    }

    public static function limitQty(int|string $customerId): int
    {
        $order = self::activeOrder($customerId);

        return (int) ($order?->limit_qty ?: $order?->packet?->limit_qty ?: 0);
    }

    /**
     * Bytes. Admin/gói nhập 1–1024 = GB; số lớn hơn = bytes sẵn.
     */
    public static function bytesFromLimit(mixed $raw): int
    {
        $s = trim((string) $raw);
        if ($s === '' || ! is_numeric($s)) {
            return 0;
        }

        $n = (float) $s;
        if ($n <= 0) {
            return 0;
        }
        if ($n <= 1024) {
            return (int) round($n * 1024 * 1024 * 1024);
        }

        return (int) $n;
    }

    public static function limitCapacity(int|string $customerId): int
    {
        $order = self::activeOrder($customerId);
        $raw = $order?->limit_capacity ?: $order?->packet?->limit_capacity ?: '0';

        return self::bytesFromLimit($raw);
    }

    public static function canAddDevice(int|string $customerId): bool
    {
        $limit = self::limitQty($customerId);
        if ($limit <= 0) {
            return false;
        }

        $used = Device::query()
            ->where('customer_id', $customerId)
            ->where(function ($q): void {
                $q->whereNull('deleted')->orWhere('deleted', '!=', 'y');
            })
            ->count();

        return $used < $limit;
    }

    public static function usedCapacity(int|string $customerId): int
    {
        return (int) ResourceFile::query()
            ->where('customer_id', $customerId)
            ->where(function ($q): void {
                $q->whereNull('deleted')->orWhere('deleted', '!=', 'y');
            })
            ->sum('file_size');
    }

    public static function canAddBytes(int|string $customerId, int $bytes, int $replacingBytes = 0): bool
    {
        return self::quotaDeny($customerId, $bytes, $replacingBytes) === null;
    }

    public static function quotaDeny(int|string $customerId, int $bytes, int $replacingBytes = 0): ?string
    {
        $limit = self::limitCapacity($customerId);
        if ($limit <= 0) {
            return 'Chưa có gói còn hạn hoặc gói không có dung lượng lưu trữ';
        }

        $used = max(0, self::usedCapacity($customerId) - max(0, $replacingBytes));
        if (($used + $bytes) > $limit) {
            return 'Hết dung lượng gói (đã dùng '.self::human($used).' / '.self::human($limit).'). Xóa file hoặc nâng gói.';
        }

        return null;
    }

    public static function hostingDeny(int $bytes): ?string
    {
        if (! DiskWatermark::canWrite($bytes)) {
            return 'Ổ đĩa hosting gần đầy, không nhận thêm video/ảnh. Liên hệ quản trị.';
        }

        $cap = (int) config('filesystems.uploads_volume_cap', 0);
        if ($cap > 0) {
            $used = (int) ResourceFile::alive()->sum('file_size');
            if (($used + $bytes) > $cap) {
                return 'Hosting đã đạt giới hạn lưu trữ ('.self::human($used).' / '.self::human($cap).'). Liên hệ quản trị.';
            }
        }

        return null;
    }

    public static function human(int $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return round($bytes / (1024 * 1024 * 1024), 1).' GB';
        }
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 1).' MB';
        }

        return $bytes.' B';
    }
}
