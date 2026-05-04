<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Recipient\Pages;

use AcMarche\AldermenAgenda\Filament\Resources\Recipient\RecipientResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateRecipient extends CreateRecord
{
    #[Override]
    protected static string $resource = RecipientResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter un destinataire';
    }
}
