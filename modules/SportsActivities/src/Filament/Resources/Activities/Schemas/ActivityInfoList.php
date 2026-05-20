<?php

namespace AcMarche\SportsActivities\Filament\Resources\Activities\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informations')
                ->columns(2)
                ->schema([
                    IconEntry::make('archived')->label('Archivée')->boolean(),
                    TextEntry::make('description')
                        ->label('Description')
                        ->columnSpanFull(),
                    TextEntry::make('groups_count')
                        ->label('Nombre de groupes')
                        ->state(fn($record): int => $record->groups()->count()),
                    TextEntry::make('registrations_count')
                        ->label('Nombre d\'inscriptions')
                        ->state(fn($record): int => $record->registrations()->count()),
                ]),
        ]);;
    }
}
