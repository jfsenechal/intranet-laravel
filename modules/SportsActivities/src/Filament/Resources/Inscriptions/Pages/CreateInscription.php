<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Inscriptions\Pages;

use AcMarche\SportsActivities\Filament\Resources\Inscriptions\InscriptionResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateInscription extends CreateRecord
{
    #[Override]
    protected static string $resource = InscriptionResource::class;

    public function getTitle(): string
    {
        return 'Nouvelle inscription';
    }
}
