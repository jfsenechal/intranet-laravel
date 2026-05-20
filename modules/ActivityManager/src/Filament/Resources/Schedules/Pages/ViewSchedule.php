<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Schedules\Pages;

use AcMarche\ActivityManager\Filament\Resources\Schedules\SchedulesResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewSchedule extends ViewRecord
{
    #[Override]
    protected static string $resource = SchedulesResource::class;

    public function getTitle(): string
    {
        return (string) $this->record->nom;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('nom')
                            ->label('Nom')
                            ->weight('bold')
                            ->columnSpanFull(),
                        TextEntry::make('activite.nom')
                            ->label('Activité')
                            ->badge()
                            ->placeholder('—'),
                        TextEntry::make('date_debut')
                            ->label('Début')
                            ->date('d/m/Y'),
                        TextEntry::make('date_fin')
                            ->label('Fin')
                            ->date('d/m/Y')
                            ->placeholder('—'),
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
