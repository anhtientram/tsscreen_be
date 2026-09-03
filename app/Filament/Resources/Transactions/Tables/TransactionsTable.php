<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Filament\Support\MoneyFormat;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_id', 'desc')
            ->columns([
                TextColumn::make('transaction_id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('reg_number')
                    ->label('Mã đơn')
                    ->searchable(),
                TextColumn::make('name_packet')
                    ->label('Gói')
                    ->searchable(),
                TextColumn::make('customer_id')
                    ->label('KH')
                    ->sortable(),
                MoneyFormat::tableColumn(
                    TextColumn::make('amount')->label('Số tiền')
                ),
                TextColumn::make('payment_date')
                    ->label('Ngày TT')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('created_date')
                    ->label('Tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
