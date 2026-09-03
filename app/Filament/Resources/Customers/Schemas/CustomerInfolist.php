<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin cá nhân')
                    ->icon('heroicon-o-user-circle')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('customer_name')->label('Họ tên')->weight('bold'),
                        TextEntry::make('phone_number')->label('SĐT')->copyable(),
                        TextEntry::make('email')->label('Email')->copyable(),
                        TextEntry::make('date_of_birth')->label('Ngày sinh')->placeholder('—'),
                        TextEntry::make('sex')->label('Giới tính')->placeholder('—'),
                        TextEntry::make('login_with')->label('Đăng nhập qua')->badge(),
                        TextEntry::make('address')->label('Địa chỉ')->columnSpanFull()->placeholder('—'),
                    ]),
                Section::make('Trạng thái')
                    ->icon('heroicon-o-signal')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('status')
                            ->label('Hoạt động')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state === 'n' ? 'Vô hiệu' : 'Hoạt động')
                            ->color(fn ($state) => $state === 'n' ? 'danger' : 'success'),
                        TextEntry::make('deleted')
                            ->label('Đã xóa')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state === 'y' ? 'Có' : 'Không')
                            ->color(fn ($state) => $state === 'y' ? 'danger' : 'gray'),
                        TextEntry::make('customer_id')->label('ID'),
                        TextEntry::make('customer_token')->label('Token media')->copyable()->columnSpanFull(),
                        TextEntry::make('created_date')->label('Ngày tạo')->dateTime('d/m/Y H:i'),
                    ]),
                Section::make('Ngân hàng')
                    ->icon('heroicon-o-building-library')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('chu_tk')->label('Chủ TK')->placeholder('—'),
                        TextEntry::make('stk')->label('STK')->placeholder('—'),
                        TextEntry::make('nganhang')->label('Ngân hàng')->placeholder('—'),
                        TextEntry::make('chinhanh')->label('Chi nhánh')->placeholder('—'),
                    ]),
            ]);
    }
}
