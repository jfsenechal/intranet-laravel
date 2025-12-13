<?php

namespace AcMarche\Document\Filament\Resources\Documents\Pages;

use AcMarche\Document\Filament\Resources\Documents\DocumentResource;
use AcMarche\Document\Filament\Resources\Documents\Schemas\DocumentInfolist;
use AcMarche\Document\Models\Document;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Storage;

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
            Actions\Action::make('download')
                ->label('Télécharger le document')
                ->icon('tabler-download')
                ->color(Color::Green)
                ->url(fn (Document $record) => Storage::disk('public')->url($record->file_path)),
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
