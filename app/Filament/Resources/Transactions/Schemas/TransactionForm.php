<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('paid_id')
                    ->numeric(),
                TextInput::make('packet_id')
                    ->numeric(),
                TextInput::make('customer_id')
                    ->required()
                    ->numeric(),
                TextInput::make('reg_number'),
                TextInput::make('name_packet'),
                TextInput::make('amount'),
                TextInput::make('payment_date'),
                TextInput::make('ref_transaction_id'),
                DateTimePicker::make('created_date'),
            ]);
    }
}
