<?php

namespace App\Filament\Resources\Accounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('account_id', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->where('deleted', '!=', 'y'))
            ->columns([
                TextColumn::make('account_id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('username')
                    ->label('Username')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->label('SĐT'),
                TextColumn::make('user_type')
                    ->label('Loại')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        '1' => 'Super',
                        '2' => 'Admin',
                        '3' => 'Member',
                        default => $state,
                    }),
                TextColumn::make('created_date')
                    ->label('Tạo')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
