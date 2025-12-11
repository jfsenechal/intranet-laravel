<?php

namespace AcMarche\Courrier\Filament\Resources\IncomingMailResource\Pages;

use AcMarche\Courrier\Filament\Resources\IncomingMailResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateIncomingMail extends CreateRecord
{
    protected static string $resource = IncomingMailResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter un courrier';
    }
}
