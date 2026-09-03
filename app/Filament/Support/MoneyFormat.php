<?php

namespace App\Filament\Support;

use App\Support\LegacyJson;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;

final class MoneyFormat
{
    public static function format(mixed $value, bool $withSuffix = true): string
    {
        $formatted = LegacyJson::money($value);

        if ($formatted === '') {
            return '—';
        }

        return $withSuffix ? "{$formatted} đ" : $formatted;
    }

    public static function tableColumn(TextColumn $column, bool $withSuffix = true): TextColumn
    {
        return $column->formatStateUsing(fn ($state) => self::format($state, $withSuffix));
    }

    public static function infolistEntry(TextEntry $entry, bool $withSuffix = true): TextEntry
    {
        return $entry->formatStateUsing(fn ($state) => self::format($state, $withSuffix));
    }

    public static function formInput(TextInput $input): TextInput
    {
        return $input
            ->helperText('Nhập số — hiển thị dạng 99.000')
            ->formatStateUsing(fn ($state) => LegacyJson::money($state))
            ->dehydrateStateUsing(fn ($state) => LegacyJson::parseMoney($state));
    }
}
