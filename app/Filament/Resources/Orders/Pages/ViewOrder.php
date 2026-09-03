<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\Packet;
use App\Services\OrderActivationService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activate')
                ->label('Kích hoạt đơn')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (Order $record) => $record->pay !== '1')
                ->form([
                    DatePicker::make('valid_date')
                        ->label('Ngày hiệu lực')
                        ->default(now())
                        ->required()
                        ->native(false),
                    DatePicker::make('payment_date')
                        ->label('Ngày thanh toán')
                        ->default(now())
                        ->required()
                        ->native(false),
                    Select::make('packet_id')
                        ->label('Gói cước')
                        ->options(fn () => Packet::query()
                            ->where(fn ($q) => $q->whereNull('deleted')->orWhere('deleted', '!=', 'y'))
                            ->pluck('name_packet', 'packet_id')
                            ->all())
                        ->default(fn (Order $record) => $record->packet_id)
                        ->searchable(),
                ])
                ->action(function (Order $record, array $data, OrderActivationService $activation): void {
                    $activation->activate(
                        $record,
                        $data['valid_date'],
                        $data['payment_date'],
                        $data['packet_id'] ?? null,
                    );
                })
                ->successNotificationTitle('Đã kích hoạt đơn hàng'),
        ];
    }
}
