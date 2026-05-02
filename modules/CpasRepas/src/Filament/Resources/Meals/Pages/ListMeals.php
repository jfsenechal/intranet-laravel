<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Meals\Pages;

use AcMarche\CpasRepas\Filament\Resources\Meals\MealResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListMeals extends ListRecords
{
    #[Override]
    protected static string $resource = MealResource::class;

    public function getTitle(): string
    {
        return 'Meals';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add meal')
                ->icon('tabler-plus'),
        ];
    }
}
