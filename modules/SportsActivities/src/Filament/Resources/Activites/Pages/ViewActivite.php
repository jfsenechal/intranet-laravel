<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Activites\Pages;

use AcMarche\SportsActivities\Filament\Resources\Activites\ActiviteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewActivite extends ViewRecord
{
    #[Override]
    protected static string $resource = ActiviteResource::class;

    public function getTitle(): string
    {
        return $this->record->nom;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('nom')->label('Nom'),
                        IconEntry::make('archive')->label('Archivée')->boolean(),
                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        TextEntry::make('groupes_count')
                            ->label('Nombre de groupes')
                            ->state(fn ($record): int => $record->groupes()->count()),
                        TextEntry::make('inscriptions_count')
                            ->label('Nombre d\'inscriptions')
                            ->state(fn ($record): int => $record->inscriptions()->count()),
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
