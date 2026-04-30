<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Rates\Pages;

use AcMarche\Mileage\Filament\Resources\Rates\RateResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateRate extends CreateRecord
{
    #[Override]
    protected static string $resource = RateResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter un tarif';
    }
}
