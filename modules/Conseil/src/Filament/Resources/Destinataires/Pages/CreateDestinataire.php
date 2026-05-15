<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Destinataires\Pages;

use AcMarche\Conseil\Filament\Resources\Destinataires\DestinataireResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateDestinataire extends CreateRecord
{
    #[Override]
    protected static string $resource = DestinataireResource::class;

    public function getTitle(): string
    {
        return 'Nouveau destinataire';
    }
}
