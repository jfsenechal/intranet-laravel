<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Residents\Pages;

use AcMarche\MealDelivery\Filament\Resources\Residents\ResidentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewResident extends ViewRecord
{
    #[Override]
    protected static string $resource = ResidentResource::class;

    public function getTitle(): string
    {
        return $this->record->fullName();
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
