<?php

namespace App\Filament\Resources\Packets;

use App\Filament\Resources\Packets\Pages\CreatePacket;
use App\Filament\Resources\Packets\Pages\EditPacket;
use App\Filament\Resources\Packets\Pages\ListPackets;
use App\Filament\Resources\Packets\Pages\ViewPacket;
use App\Filament\Resources\Packets\Schemas\PacketForm;
use App\Filament\Resources\Packets\Schemas\PacketInfolist;
use App\Filament\Resources\Packets\Tables\PacketsTable;
use App\Models\Packet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PacketResource extends Resource
{
    protected static ?string $model = Packet::class;

    protected static ?string $navigationLabel = 'Gói cước';

    protected static ?string $modelLabel = 'Gói cước';

    protected static ?string $pluralModelLabel = 'Gói cước';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'name_packet';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PacketForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PacketInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PacketsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPackets::route('/'),
            'create' => CreatePacket::route('/create'),
            'view' => ViewPacket::route('/{record}'),
            'edit' => EditPacket::route('/{record}/edit'),
        ];
    }
}
