<?php

namespace App\Filament\Resources\Packets\Pages;

use App\Filament\Resources\Packets\PacketResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPackets extends ListRecords
{
    protected static string $resource = PacketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
