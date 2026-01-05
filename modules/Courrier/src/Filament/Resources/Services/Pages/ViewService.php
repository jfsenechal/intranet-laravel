<?php

namespace AcMarche\Courrier\Filament\Resources\Services\Pages;

use AcMarche\Courrier\Filament\Resources\Services\Schemas\ServiceInfolist;
use AcMarche\Courrier\Filament\Resources\Services\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

final class ViewService extends ViewRecord
{
    protected static string $resource = ServiceResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return ServiceInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('tabler-edit'),
            Actions\DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
