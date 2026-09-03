<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('customer_id', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->withCount('devices'))
            ->columns([
                TextColumn::make('customer_id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->label('SĐT')
                    ->searchable(),
                TextColumn::make('devices_count')
                    ->label('TV')
                    ->sortable(),
                IconColumn::make('status')
                    ->label('Hoạt động')
                    ->boolean(fn ($state) => $state !== 'n'),
                TextColumn::make('created_date')
                    ->label('Tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(['y' => 'Hoạt động', 'n' => 'Vô hiệu']),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('toggleStatus')
                    ->label(fn (Customer $record) => ($record->deleted === 'y' || $record->status === 'n') ? 'Bật' : 'Tắt')
                    ->icon('heroicon-o-power')
                    ->color(fn (Customer $record) => ($record->deleted === 'y' || $record->status === 'n') ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(function (Customer $record): void {
                        $disabled = $record->deleted === 'y' || $record->status === 'n';
                        if ($disabled) {
                            $record->status = 'y';
                            $record->deleted = 'n';
                        } else {
                            $record->status = 'n';
                        }
                        $record->save();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
