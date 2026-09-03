<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Hoạt động')
                ->modifyQueryUsing(fn ($query) => $query
                    ->where(fn ($q) => $q->whereNull('deleted')->orWhere('deleted', '!=', 'y'))
                    ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'n'))),
            'disabled' => Tab::make('Vô hiệu / đã xóa')
                ->modifyQueryUsing(fn ($query) => $query
                    ->where(fn ($q) => $q->where('deleted', 'y')->orWhere('status', 'n')))
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
