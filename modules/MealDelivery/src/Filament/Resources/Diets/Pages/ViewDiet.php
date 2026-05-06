<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Diets\Pages;

use AcMarche\MealDelivery\Filament\Resources\Diets\DietResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewDiet extends ViewRecord
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
            EditAction::make()
                ->icon(Heroicon::Pencil),
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
