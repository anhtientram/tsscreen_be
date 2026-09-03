<?php

namespace App\Filament\Resources\Packets\Schemas;

use App\Filament\Support\MoneyFormat;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PacketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name_packet')
                    ->label('Tên gói')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                MoneyFormat::formInput(
                    TextInput::make('price')->label('Giá 1 tháng (VNĐ)')->default('0')
                ),
                MoneyFormat::formInput(
                    TextInput::make('price_6_month')->label('Giá 6 tháng')->default('0')
                ),
                MoneyFormat::formInput(
                    TextInput::make('price_12_month')->label('Giá 12 tháng')->default('0')
                ),
                TextInput::make('day_qty')
                    ->label('Số ngày (dùng thử)')
                    ->numeric()
                    ->default('0')
                    ->helperText('Gói dùng thử: số ngày hết hạn tính từ ngày đăng ký'),
                TextInput::make('month_qty')
                    ->label('Số tháng')
                    ->numeric()
                    ->default('0'),
                TextInput::make('year_qty')
                    ->label('Số năm')
                    ->numeric()
                    ->default('0'),
                TextInput::make('limit_qty')
                    ->label('Số TV tối đa')
                    ->numeric()
                    ->default('1')
                    ->required(),
                TextInput::make('limit_capacity')
                    ->label('Dung lượng media (GB)')
                    ->numeric()
                    ->default('1')
                    ->helperText('1–1024 = GB; giống API admin cũ'),
                Toggle::make('is_trial')
                    ->label('Gói dùng thử')
                    ->default(false)
                    ->formatStateUsing(fn ($state) => $state === '1' || $state === true)
                    ->dehydrateStateUsing(fn ($state) => $state ? '1' : '0'),
                Toggle::make('is_business')
                    ->label('Gói doanh nghiệp')
                    ->default(false)
                    ->formatStateUsing(fn ($state) => $state === '1' || $state === true)
                    ->dehydrateStateUsing(fn ($state) => $state ? '1' : '0'),
                Textarea::make('detail')
                    ->label('Chi tiết')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Mô tả')
                    ->columnSpanFull(),
                TextInput::make('picture')
                    ->label('Ảnh (URL)')
                    ->columnSpanFull(),
            ]);
    }
}
