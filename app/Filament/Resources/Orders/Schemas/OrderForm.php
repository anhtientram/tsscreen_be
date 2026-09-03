<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('packet_id')
                    ->relationship('packet', 'packet_id'),
                Select::make('customer_id')
                    ->relationship('customer', 'customer_id')
                    ->required(),
                TextInput::make('packet_code'),
                TextInput::make('reg_number'),
                TextInput::make('name_packet'),
                TextInput::make('price'),
                TextInput::make('price_6_month'),
                TextInput::make('price_12_month'),
                TextInput::make('day_qty'),
                TextInput::make('month_qty'),
                TextInput::make('year_qty'),
                TextInput::make('pay_month'),
                TextInput::make('is_trial')
                    ->required()
                    ->default('0'),
                TextInput::make('is_business')
                    ->required()
                    ->default('0'),
                Textarea::make('detail')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('picture'),
                TextInput::make('pay')
                    ->required()
                    ->default('0'),
                TextInput::make('type'),
                TextInput::make('type_pay'),
                TextInput::make('register_date'),
                TextInput::make('payment_date'),
                TextInput::make('valid_date'),
                TextInput::make('expire_date'),
                TextInput::make('payment_due_date'),
                TextInput::make('limit_capacity'),
                TextInput::make('limit_qty'),
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
