<?php

namespace AcMarche\Publication\Filament\Resources\PublicationResource\Pages;

use AcMarche\Publication\Filament\Resources\PublicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewPublication extends ViewRecord
{
    protected static string $resource = PublicationResource::class;

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
