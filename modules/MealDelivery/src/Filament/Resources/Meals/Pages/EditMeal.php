<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Meals\Pages;

use AcMarche\MealDelivery\Filament\Resources\Meals\MealResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditMeal extends EditRecord
{
    #[Override]
    protected static string $resource = MealResource::class;

    public function getTitle(): string
    {
        return 'Edit meal';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
