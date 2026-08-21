<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Order;
use App\Models\ResourceFile;

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

    public static function limitCapacity(int|string $customerId): int
    {
        $order = self::activeOrder($customerId);

        return (int) ($order?->limit_capacity ?: $order?->packet?->limit_capacity ?: 0);
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

    public static function canAddBytes(int|string $customerId, int $bytes): bool
    {
        $limit = self::limitCapacity($customerId);
        if ($limit <= 0) {
            return false;
        }

        return (self::usedCapacity($customerId) + $bytes) <= $limit;
    }
}
