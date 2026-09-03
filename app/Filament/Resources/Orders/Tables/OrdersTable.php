<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Support\MoneyFormat;
use App\Models\Order;
use App\Models\Packet;
use App\Services\OrderActivationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('paid_id', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['customer', 'packet']))
            ->columns([
                TextColumn::make('paid_id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('reg_number')
                    ->label('Mã đơn')
                    ->searchable(),
                TextColumn::make('name_packet')
                    ->label('Gói')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('customer.customer_name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('customer.phone_number')
                    ->label('SĐT')
                    ->searchable(),
                MoneyFormat::tableColumn(
                    TextColumn::make('price')->label('Giá')
                ),
                TextColumn::make('pay')
                    ->label('TT')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === '1' ? 'Active' : 'Chờ')
                    ->color(fn ($state) => $state === '1' ? 'success' : 'warning'),
                TextColumn::make('register_date')
                    ->label('ĐK')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('valid_date')
                    ->label('Hiệu lực')
                    ->date('d/m/Y')
                    ->toggleable(),
                TextColumn::make('expire_date')
                    ->label('Hết hạn')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('is_trial')
                    ->label('Thử')
                    ->formatStateUsing(fn ($state) => $state === '1' ? 'Có' : '')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('pay')
                    ->label('Trạng thái')
                    ->options(['0' => 'Chờ kích hoạt', '1' => 'Đã kích hoạt']),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('activate')
                    ->label('Kích hoạt')
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
