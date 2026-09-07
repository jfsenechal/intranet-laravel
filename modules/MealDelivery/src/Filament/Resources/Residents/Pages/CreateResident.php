<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Residents\Pages;

use AcMarche\MealDelivery\Filament\Resources\Residents\ResidentResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateResident extends CreateRecord
{
    #[Override]
    protected static string $resource = ResidentResource::class;

    public function getTitle(): string
    {
        return 'Ajouter un résident';
    }
}
