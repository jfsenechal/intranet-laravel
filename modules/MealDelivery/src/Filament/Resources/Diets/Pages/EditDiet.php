<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Diets\Pages;

use AcMarche\MealDelivery\Filament\Resources\Diets\DietResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditDiet extends EditRecord
{
    #[Override]
    protected static string $resource = DietResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon(Heroicon::Eye),
        ];
    }
}
