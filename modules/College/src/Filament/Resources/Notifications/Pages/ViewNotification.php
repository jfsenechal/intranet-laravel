<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Notifications\Pages;

use AcMarche\College\Filament\Resources\Notifications\NotificationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewNotification extends ViewRecord
{
    #[Override]
    protected static string $resource = NotificationResource::class;

    public function getTitle(): string
    {
        return (string) $this->record->file_name;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Document')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('file_name')->label('Nom du fichier'),
                        TextEntry::make('mime')->label('Type MIME'),
                        TextEntry::make('updatedAt')->label('Modifié le')->dateTime(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Modifier')
                ->icon(Heroicon::PencilSquare),
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }
}
