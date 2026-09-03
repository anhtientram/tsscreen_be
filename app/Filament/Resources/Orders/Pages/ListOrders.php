<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        $today = now()->format('Y-m-d');
        $notDeleted = fn ($q) => $q->whereNull('deleted')->orWhere('deleted', '!=', 'y');

        return [
            'all' => Tab::make('Tất cả')
                ->modifyQueryUsing(fn ($query) => $query->where($notDeleted))
                ->badge(Order::query()->where($notDeleted)->count()),
            'pending' => Tab::make('Chờ kích hoạt')
                ->modifyQueryUsing(fn ($query) => $query->where('pay', '0')->where($notDeleted))
                ->badge(Order::query()->where('pay', '0')->where($notDeleted)->count())
                ->badgeColor('warning'),
            'active' => Tab::make('Đang hoạt động')
                ->modifyQueryUsing(fn ($query) => $query
                    ->where('pay', '1')
                    ->where($notDeleted)
                    ->where(fn ($q) => $q->whereNull('expire_date')->orWhere('expire_date', '>=', $today)))
                ->badgeColor('success'),
            'expired' => Tab::make('Hết hạn')
                ->modifyQueryUsing(fn ($query) => $query
                    ->where('pay', '1')
                    ->where($notDeleted)
                    ->whereNotNull('expire_date')
                    ->where('expire_date', '!=', '')
                    ->where('expire_date', '<', $today))
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
