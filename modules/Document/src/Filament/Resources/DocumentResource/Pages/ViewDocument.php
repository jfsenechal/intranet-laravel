<?php

namespace AcMarche\Document\Filament\Resources\DocumentResource\Pages;

use AcMarche\Document\Filament\Resources\DocumentResource;
use AcMarche\Document\Filament\Resources\DocumentResource\Schema\DocumentInfolist;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

final class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return DocumentInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour à la liste')
                ->icon('tabler-list')
                ->url(DocumentResource::getUrl('index')),
            Actions\EditAction::make()
                ->icon('tabler-edit'),
            Actions\DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
