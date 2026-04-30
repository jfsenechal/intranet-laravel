<?php

declare(strict_types=1);

namespace AcMarche\Document\Filament\Resources\Documents\Pages;

use AcMarche\Document\Filament\Resources\Documents\DocumentResource;
use AcMarche\Document\Filament\Resources\Documents\Schemas\DocumentInfolist;
use AcMarche\Document\Models\Document;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Storage;
use Override;

final class ViewDocument extends ViewRecord
{
    #[Override]
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
            Action::make('download')
                ->label('Télécharger le document')
                ->icon('tabler-download')
                ->color(Color::Green)
                ->url(fn (Document $record) => Storage::disk('public')->url($record->file_path)),
            Action::make('back')
                ->label('Retour à la liste')
                ->icon('tabler-list')
                ->url(DocumentResource::getUrl('index')),
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
