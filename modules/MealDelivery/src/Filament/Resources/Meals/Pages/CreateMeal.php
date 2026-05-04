<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Meals\Pages;

use AcMarche\MealDelivery\Filament\Resources\Meals\MealResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateMeal extends CreateRecord
{
    #[Override]
    protected static string $resource = MealResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Add meal';
    }
}
