<?php

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Pages;

use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
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
