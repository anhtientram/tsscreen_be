<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('username')
                    ->label('Tên đăng nhập')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('Mật khẩu')
                    ->password()
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText('Lưu bcrypt(MD5) — giống API admin cũ'),
                TextInput::make('email')
                    ->label('Email')
                    ->email(),
                TextInput::make('phone_number')
                    ->label('SĐT'),
                Select::make('user_type')
                    ->label('Loại')
                    ->options([
                        '1' => 'Super Admin',
                        '2' => 'Admin',
                        '3' => 'Member',
                    ])
                    ->default('2')
                    ->required(),
            ]);
    }
}
