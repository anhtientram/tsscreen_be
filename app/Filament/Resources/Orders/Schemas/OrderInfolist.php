<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Filament\Support\MoneyFormat;
use App\Services\PacketQuota;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Đơn hàng')
                    ->icon('heroicon-o-shopping-bag')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('reg_number')->label('Mã đơn')->copyable()->weight('bold'),
                        TextEntry::make('paid_id')->label('ID'),
                        TextEntry::make('pay')
                            ->label('Trạng thái')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state === '1' ? 'Đã kích hoạt' : 'Chờ duyệt')
                            ->color(fn ($state) => $state === '1' ? 'success' : 'warning'),
                        TextEntry::make('name_packet')->label('Gói cước')->columnSpan(2),
                        TextEntry::make('type')->label('Loại đơn')->badge(),
                        MoneyFormat::infolistEntry(
                            TextEntry::make('price')->label('Giá')
                        ),
                        TextEntry::make('pay_month')->label('Tháng trả')->placeholder('—'),
                        TextEntry::make('is_trial')
                            ->label('Dùng thử')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state === '1' ? 'Có' : 'Không')
                            ->color(fn ($state) => $state === '1' ? 'info' : 'gray'),
                    ]),
                Section::make('Khách hàng')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('customer.customer_name')->label('Tên')->placeholder('—'),
                        TextEntry::make('customer.phone_number')->label('SĐT')->copyable(),
                        TextEntry::make('customer.email')->label('Email')->placeholder('—'),
                        TextEntry::make('customer_id')->label('Customer ID'),
                    ]),
                Section::make('Thời hạn')
                    ->icon('heroicon-o-calendar')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('register_date')->label('Ngày đăng ký')->date('d/m/Y'),
                        TextEntry::make('payment_date')->label('Thanh toán')->date('d/m/Y'),
                        TextEntry::make('valid_date')->label('Hiệu lực')->date('d/m/Y'),
                        TextEntry::make('expire_date')
                            ->label('Hết hạn')
                            ->date('d/m/Y')
                            ->color(fn ($state) => $state && $state < now()->format('Y-m-d') ? 'danger' : 'success'),
                    ]),
                Section::make('Hạn mức gói')
                    ->icon('heroicon-o-scale')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('limit_qty')->label('Số TV'),
                        TextEntry::make('limit_capacity')
                            ->label('Dung lượng media')
                            ->formatStateUsing(function ($state) {
                                if (! $state) {
                                    return '—';
                                }
                                $bytes = (int) PacketQuota::bytesFromLimit((string) $state);
                                $gb = round($bytes / (1024 * 1024 * 1024), 2);

                                return $gb >= 1 ? "{$gb} GB" : number_format($bytes).' bytes';
                            }),
                        TextEntry::make('day_qty')->label('Ngày (thử)'),
                        TextEntry::make('month_qty')->label('Tháng'),
                    ]),
                Section::make('Ghi chú')
                    ->collapsed()
                    ->schema([
                        Grid::make(1)->schema([
                            TextEntry::make('detail')->label('Chi tiết')->placeholder('—'),
                            TextEntry::make('description')->label('Mô tả')->placeholder('—'),
                        ]),
                    ]),
            ]);
    }
}
