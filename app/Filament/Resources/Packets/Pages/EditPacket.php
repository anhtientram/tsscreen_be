<?php

namespace App\Filament\Resources\Packets\Pages;

use App\Filament\Resources\Packets\PacketResource;
use App\Services\PacketQuota;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPacket extends EditRecord
{
    protected static string $resource = PacketResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! empty($data['limit_capacity'])) {
            $bytes = (int) PacketQuota::bytesFromLimit((string) $data['limit_capacity']);
            $data['limit_capacity'] = (string) max(1, (int) round($bytes / (1024 * 1024 * 1024)));
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['limit_capacity'] = (string) PacketQuota::bytesFromLimit((string) ($data['limit_capacity'] ?? '0'));

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            Action::make('softDelete')
                ->label('Ẩn gói')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->update(['deleted' => 'y']);
                    $this->redirect(PacketResource::getUrl('index'));
                }),
        ];
    }
}
