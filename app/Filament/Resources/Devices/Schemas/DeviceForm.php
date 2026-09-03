<?php

namespace App\Filament\Resources\Devices\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DeviceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('computer_name'),
                TextInput::make('seri_computer')
                    ->required(),
                TextInput::make('ip_address'),
                TextInput::make('status')
                    ->required()
                    ->default('1'),
                TextInput::make('provinces'),
                TextInput::make('district'),
                TextInput::make('wards'),
                TextInput::make('center_id'),
                TextInput::make('location'),
                TextInput::make('actived_date'),
                TextInput::make('ultraviewPW'),
                TextInput::make('ultraviewID'),
                Select::make('customer_id')
                    ->relationship('customer', 'customer_id')
                    ->required(),
                TextInput::make('customer_name'),
                TextInput::make('type'),
                TextInput::make('id_dir')
                    ->numeric(),
                TextInput::make('time_end'),
                TextInput::make('turn_on')
                    ->required()
                    ->default('0'),
                TextInput::make('turn_off')
                    ->required()
                    ->default('0'),
                TextInput::make('user'),
                TextInput::make('pass'),
                TextInput::make('isCheckOnProjector')
                    ->required()
                    ->default('0'),
                TextInput::make('isCheckOffProjector')
                    ->required()
                    ->default('0'),
                TextInput::make('rom_memory_total'),
                TextInput::make('rom_memory_used'),
                DateTimePicker::make('lasted_alive_time'),
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
