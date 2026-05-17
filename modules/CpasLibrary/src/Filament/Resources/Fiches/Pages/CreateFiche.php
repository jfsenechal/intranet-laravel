<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages;

use AcMarche\CpasLibrary\Filament\Resources\Fiches\FicheResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateFiche extends CreateRecord
{
    #[Override]
    protected static string $resource = FicheResource::class;

    public function getTitle(): string
    {
        return 'Nouvelle fiche';
    }
}
