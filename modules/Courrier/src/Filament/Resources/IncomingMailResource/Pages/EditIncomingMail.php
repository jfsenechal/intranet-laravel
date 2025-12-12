<?php

namespace AcMarche\Courrier\Filament\Resources\IncomingMailResource\Pages;

use AcMarche\Courrier\Filament\Resources\IncomingMailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditIncomingMail extends EditRecord
{
    protected static string $resource = IncomingMailResource::class;

    public function getTitle(): string
    {
        return 'Modifier le courrier';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
