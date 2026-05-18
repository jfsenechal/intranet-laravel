<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\Incidents\Pages;

use AcMarche\StreetWatch\Filament\Resources\Incidents\IncidentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewIncident extends ViewRecord
{
    #[Override]
    protected static string $resource = IncidentResource::class;

    public function getTitle(): string
    {
        return (string) $this->record->object;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('place')
                            ->label('Lieu')
                            ->weight('bold'),
                        TextEntry::make('object')
                            ->label('Objet')
                            ->columnSpanFull(),
                        TextEntry::make('typeIncident.name')
                            ->label('Type')
                            ->badge(),
                        TextEntry::make('requestBy.name')
                            ->label('Demandé par'),
                        TextEntry::make('occurred_date')
                            ->label("Date de l'incident")
                            ->dateTime('d/m/Y'),
                    ]),

                Section::make('Contenu')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('description')
                            ->label('Description')
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('response')
                            ->label('Suite donnée')
                            ->html()
                            ->columnSpanFull()
                            ->placeholder('—'),
                    ]),

                Section::make('Méta')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user_add')->label('Auteur'),
                        TextEntry::make('createdAt')->label('Créé le')->dateTime(),
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
