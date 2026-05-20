<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Activities\Pages;

use AcMarche\SportsActivities\Filament\Resources\Activities\ActivityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewActivity extends ViewRecord
{
    #[Override]
    protected static string $resource = ActivityResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Nom'),
                        IconEntry::make('archived')->label('Archivée')->boolean(),
                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        TextEntry::make('groups_count')
                            ->label('Nombre de groupes')
                            ->state(fn ($record): int => $record->groups()->count()),
                        TextEntry::make('registrations_count')
                            ->label('Nombre d\'inscriptions')
                            ->state(fn ($record): int => $record->registrations()->count()),
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
