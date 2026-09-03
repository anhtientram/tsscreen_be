<?php

namespace App\Filament\Resources\Devices\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeviceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thiết bị TV')
                    ->icon('heroicon-o-tv')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('computer_name')->label('Tên máy')->weight('bold'),
                        TextEntry::make('computer_id')->label('ID'),
                        TextEntry::make('status')->label('Trạng thái')->badge(),
                        TextEntry::make('seri_computer')->label('Serial')->copyable()->columnSpan(2),
                        TextEntry::make('ip_address')->label('IP')->copyable(),
                    ]),
                Section::make('Khách hàng & nhóm')
                    ->icon('heroicon-o-user-group')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('customer.customer_name')->label('Khách hàng')->placeholder('—'),
                        TextEntry::make('customer.phone_number')->label('SĐT')->placeholder('—'),
                        TextEntry::make('id_dir')->label('ID nhóm (dir)')->placeholder('—'),
                        TextEntry::make('type')->label('Loại')->badge(),
                    ]),
                Section::make('Trạng thái online')
                    ->icon('heroicon-o-signal')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('lasted_alive_time')
                            ->label('Heartbeat cuối')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—')
                            ->color(fn ($state) => $state && $state >= now()->subMinutes(2) ? 'success' : 'gray'),
                        TextEntry::make('rom_memory_used')->label('ROM đã dùng')->placeholder('—'),
                        TextEntry::make('rom_memory_total')->label('ROM tổng')->placeholder('—'),
                        TextEntry::make('created_date')->label('Tạo lúc')->dateTime('d/m/Y H:i'),
                        TextEntry::make('actived_date')->label('Kích hoạt')->placeholder('—'),
                    ]),
            ]);
    }
}
