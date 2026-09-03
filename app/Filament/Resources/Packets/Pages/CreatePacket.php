<?php

namespace App\Filament\Resources\Packets\Pages;

use App\Filament\Resources\Packets\PacketResource;
use App\Services\PacketQuota;
use Filament\Resources\Pages\CreateRecord;

class CreatePacket extends CreateRecord
{
    protected static string $resource = PacketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['limit_capacity'] = (string) PacketQuota::bytesFromLimit((string) ($data['limit_capacity'] ?? '0'));
        $data['deleted'] = 'n';

        return $data;
    }
}
