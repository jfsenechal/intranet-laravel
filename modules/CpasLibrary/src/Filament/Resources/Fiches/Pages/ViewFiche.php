<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages;

use AcMarche\CpasLibrary\Filament\Resources\Fiches\FicheResource;
use AcMarche\CpasLibrary\Models\Fiche;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Override;

final class ViewFiche extends ViewRecord
{
    #[Override]
    protected static string $resource = FicheResource::class;

    public function getTitle(): string
    {
        return (string) $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nom')
                            ->weight('bold')
                            ->columnSpanFull(),
                        TextEntry::make('category.name')
                            ->label('Catégorie')
                            ->badge(),
                        TextEntry::make('type')
                            ->label('Type')
                            ->badge(),
                        TextEntry::make('tags.name')
                            ->label('Tags')
                            ->badge()
                            ->separator(','),
                        TextEntry::make('slug')->label('Slug'),
                    ]),

                Section::make('Contenu')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('description')
                            ->label('Description')
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('source')
                            ->label('Source')
                            ->url(fn (?string $state): ?string => $state, shouldOpenInNewTab: true),
                        TextEntry::make('fileName')->label('Fichier'),
                        TextEntry::make('mimeType')->label('Type MIME'),
                        TextEntry::make('fileSize')
                            ->label('Taille')
                            ->numeric()
                            ->suffix(' o'),
                    ]),

                Section::make('Dates')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('createdAt')->label('Créé le')->dateTime(),
                        TextEntry::make('updatedAt')->label('Modifié le')->dateTime(),
                        TextEntry::make('userAdd')->label('Auteur'),
                        TextEntry::make('date_promulgation')->label('Promulgation')->date(),
                        TextEntry::make('date_publication')->label('Publication')->date(),
                        TextEntry::make('date_rappel')->label('Rappel')->date(),
                        TextEntry::make('date_begin')->label('Début')->date(),
                        TextEntry::make('date_end')->label('Fin')->date(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Modifier')
                ->icon(Heroicon::PencilSquare),
            Action::make('download')
                ->label('Télécharger')
                ->icon(Heroicon::ArrowDownTray)
                ->visible(fn (Fiche $record): bool => $record->fileName !== null)
                ->action(fn (Fiche $record) => Storage::disk('cpas-library')->download(
                    'fiches/'.$record->fileName,
                    $record->fileName,
                )),
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }
}
