<?php

namespace App\Filament\Resources\Devices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DevicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('computer_id', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('customer'))
            ->columns([
                TextColumn::make('computer_id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('computer_name')
                    ->label('Tên TV')
                    ->searchable(),
                TextColumn::make('seri_computer')
                    ->label('Serial')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('customer.customer_name')
                    ->label('Khách')
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('TT')
                    ->badge(),
                TextColumn::make('lasted_alive_time')
                    ->label('Heartbeat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('created_date')
                    ->label('Tạo')
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
