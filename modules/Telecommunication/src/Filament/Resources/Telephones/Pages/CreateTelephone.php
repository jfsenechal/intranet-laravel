<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Filament\Resources\Telephones\Pages;

use AcMarche\Telecommunication\Filament\Resources\Telephones\TelephoneResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateTelephone extends CreateRecord
{
    #[Override]
    protected static string $resource = TelephoneResource::class;

    public function getTitle(): string
    {
        return 'Nouveau téléphone';
    }
}
