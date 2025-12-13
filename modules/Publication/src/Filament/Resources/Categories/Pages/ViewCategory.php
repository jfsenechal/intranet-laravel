<?php

namespace AcMarche\Publication\Filament\Resources\Categories\Pages;

use AcMarche\Publication\Filament\Resources\Categories\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('tabler-edit'),
            Actions\DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }

    public function getTitle(): string
    {
        return $this->record->name;
    }
}
