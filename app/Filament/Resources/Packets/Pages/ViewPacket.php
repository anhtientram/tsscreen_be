<?php

namespace App\Filament\Resources\Packets\Pages;

use App\Filament\Resources\Packets\PacketResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPacket extends ViewRecord
{
    protected static string $resource = PacketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
