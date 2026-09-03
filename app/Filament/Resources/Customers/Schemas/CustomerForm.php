<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('customer_name'),
                TextInput::make('address'),
                TextInput::make('phone_number')
                    ->tel(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('date_of_birth'),
                TextInput::make('sex'),
                TextInput::make('chu_tk'),
                TextInput::make('stk'),
                TextInput::make('nganhang'),
                TextInput::make('chinhanh'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('login_with')
                    ->required()
                    ->default('email'),
                TextInput::make('status')
                    ->required()
                    ->default('y'),
                TextInput::make('deleted')
                    ->required()
                    ->default('n'),
                TextInput::make('created_by'),
                DateTimePicker::make('created_date'),
                TextInput::make('last_MDF_by'),
                DateTimePicker::make('last_MDF_date'),
            ]);
    }
}
