<?php

namespace AcMarche\Courrier\Filament\Resources\Recipients\Pages;

use AcMarche\Courrier\Filament\Resources\Recipients\RecipientResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRecipient extends CreateRecord
{
    protected static string $resource = RecipientResource::class;

    public function getTitle(): string
    {
        return 'Ajouter un destinataire';
    }
}
