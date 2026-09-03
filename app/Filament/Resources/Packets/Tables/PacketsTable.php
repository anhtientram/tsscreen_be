<?php

namespace App\Filament\Resources\Packets\Tables;

use App\Filament\Support\MoneyFormat;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PacketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('packet_id', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->where(fn ($q) => $q->whereNull('deleted')->orWhere('deleted', '!=', 'y')))
            ->columns([
                TextColumn::make('packet_id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name_packet')
                    ->label('Tên gói')
                    ->searchable()
                    ->sortable(),
                MoneyFormat::tableColumn(
                    TextColumn::make('price')->label('Giá/tháng')
                ),
                TextColumn::make('day_qty')
                    ->label('Ngày')
                    ->toggleable(),
                TextColumn::make('month_qty')
                    ->label('Tháng')
                    ->toggleable(),
                TextColumn::make('limit_qty')
                    ->label('TV'),
                IconColumn::make('is_trial')
                    ->label('Thử')
                    ->boolean(fn ($state) => $state === '1'),
                IconColumn::make('is_business')
                    ->label('DN')
                    ->boolean(fn ($state) => $state === '1'),
            ])
            ->filters([
                SelectFilter::make('is_trial')
                    ->label('Loại')
                    ->options(['1' => 'Dùng thử', '0' => 'Trả phí']),
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
