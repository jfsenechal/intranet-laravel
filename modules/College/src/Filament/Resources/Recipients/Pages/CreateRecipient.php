<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Recipients\Pages;

use AcMarche\College\Filament\Resources\Recipients\RecipientResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateRecipient extends CreateRecord
{
    #[Override]
    protected static string $resource = RecipientResource::class;

    public function getTitle(): string
    {
        return 'Nouveau destinataire';
    }
}
