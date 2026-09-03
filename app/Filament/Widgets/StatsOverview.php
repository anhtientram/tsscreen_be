<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Packet;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $today = now()->format('Y-m-d');
        $notDeleted = fn ($q) => $q->whereNull('deleted')->orWhere('deleted', '!=', 'y');

        $pendingOrders = Order::query()->where('pay', '0')->where($notDeleted)->count();
        $activeOrders = Order::query()
            ->where('pay', '1')->where($notDeleted)
            ->where(fn ($q) => $q->whereNull('expire_date')->orWhere('expire_date', '>=', $today))
            ->count();
        $expiredOrders = Order::query()
            ->where('pay', '1')->where($notDeleted)
            ->whereNotNull('expire_date')->where('expire_date', '!=', '')->where('expire_date', '<', $today)
            ->count();
        $activeCustomers = Customer::query()
            ->where($notDeleted)
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'n'))
            ->count();
        $packets = Packet::query()->where($notDeleted)->count();

        $orderTrend = $this->registrationsLast7Days();

        return [
            Stat::make('Đơn chờ kích hoạt', (string) $pendingOrders)
                ->description($pendingOrders > 0 ? 'Cần xử lý' : 'Không có đơn chờ')
                ->descriptionIcon($pendingOrders > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle)
                ->color('warning')
                ->icon(Heroicon::OutlinedClock)
                ->chart($orderTrend),
            Stat::make('Gói đang hoạt động', (string) $activeOrders)
                ->description('pay = 1, còn hạn')
                ->descriptionIcon(Heroicon::OutlinedSignal)
                ->color('success')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->chart($orderTrend),
            Stat::make('Gói đã hết hạn', (string) $expiredOrders)
                ->description('expire_date < hôm nay')
                ->descriptionIcon(Heroicon::OutlinedCalendarDays)
                ->color('danger')
                ->icon(Heroicon::OutlinedXCircle),
            Stat::make('Khách hoạt động', (string) $activeCustomers)
                ->description('Tài khoản đang dùng')
                ->color('info')
                ->icon(Heroicon::OutlinedUsers),
            Stat::make('Gói cước', (string) $packets)
                ->description('Catalog đang bán')
                ->color('primary')
                ->icon(Heroicon::OutlinedCube),
        ];
    }

    /** @return list<int> */
    private function registrationsLast7Days(): array
    {
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $counts[] = Order::query()
                ->where(fn ($q) => $q->where('register_date', $date)->orWhereDate('created_date', $date))
                ->count();
        }

        return $counts;
    }
}
