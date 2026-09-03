<?php

namespace App\Filament\Resources\Packets\Schemas;

use App\Filament\Support\MoneyFormat;
use App\Services\PacketQuota;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PacketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Gói cước')
                    ->icon('heroicon-o-cube')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name_packet')->label('Tên gói')->weight('bold')->columnSpan(2),
                        TextEntry::make('packet_id')->label('ID'),
                        MoneyFormat::infolistEntry(
                            TextEntry::make('price')->label('Giá/tháng')
                        ),
                        MoneyFormat::infolistEntry(
                            TextEntry::make('price_6_month')->label('6 tháng')
                        ),
                        MoneyFormat::infolistEntry(
                            TextEntry::make('price_12_month')->label('12 tháng')
                        ),
                        TextEntry::make('is_trial')
                            ->label('Dùng thử')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state === '1' ? 'Có' : 'Không')
                            ->color(fn ($state) => $state === '1' ? 'info' : 'gray'),
                        TextEntry::make('is_business')
                            ->label('Doanh nghiệp')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state === '1' ? 'Có' : 'Không'),
                    ]),
                Section::make('Thời hạn & hạn mức')
                    ->icon('heroicon-o-clock')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('day_qty')->label('Ngày (thử)'),
                        TextEntry::make('month_qty')->label('Tháng'),
                        TextEntry::make('year_qty')->label('Năm'),
                        TextEntry::make('limit_qty')->label('Số TV tối đa'),
                        TextEntry::make('limit_capacity')
                            ->label('Dung lượng')
                            ->formatStateUsing(function ($state) {
                                if (! $state) {
                                    return '—';
                                }
                                $bytes = (int) PacketQuota::bytesFromLimit((string) $state);
                                $gb = round($bytes / (1024 * 1024 * 1024), 2);

                                return $gb >= 1 ? "{$gb} GB" : number_format($bytes).' bytes';
                            })
                            ->columnSpan(2),
                    ]),
                Section::make('Mô tả')
                    ->collapsed()
                    ->schema([
                        TextEntry::make('detail')->label('Chi tiết')->placeholder('—'),
                        TextEntry::make('description')->label('Mô tả')->placeholder('—'),
                    ]),
            ]);
    }
}
