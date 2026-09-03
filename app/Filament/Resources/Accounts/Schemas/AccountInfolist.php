<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Tài khoản admin')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('username')->label('Username')->weight('bold')->copyable(),
                        TextEntry::make('account_id')->label('ID'),
                        TextEntry::make('email')->label('Email')->copyable()->placeholder('—'),
                        TextEntry::make('phone_number')->label('SĐT')->placeholder('—'),
                        TextEntry::make('user_type')
                            ->label('Quyền')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                '1' => 'Super Admin',
                                '2' => 'Admin',
                                '3' => 'Member',
                                default => (string) $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                '1' => 'danger',
                                '2' => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('deleted')
                            ->label('Trạng thái')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state === 'y' ? 'Vô hiệu' : 'Hoạt động')
                            ->color(fn ($state) => $state === 'y' ? 'danger' : 'success'),
                        TextEntry::make('created_date')->label('Tạo lúc')->dateTime('d/m/Y H:i'),
                        TextEntry::make('last_MDF_date')->label('Cập nhật')->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
