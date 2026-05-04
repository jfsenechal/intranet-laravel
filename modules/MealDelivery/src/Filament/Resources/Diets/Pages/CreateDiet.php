<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Diets\Pages;

use AcMarche\MealDelivery\Filament\Resources\Diets\DietResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateDiet extends CreateRecord
{
    #[Override]
    protected static string $resource = DietResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Add diet';
    }
}
