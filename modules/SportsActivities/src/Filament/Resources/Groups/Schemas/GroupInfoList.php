<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Activities\Schemas;

use AcMarche\SportsActivities\Models\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class GroupInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            RepeatableEntry::make('groups')
                ->hiddenLabel()
                ->contained(false)
                ->schema([
                    Section::make()
                        ->heading(fn (Group $record): string => $record->name())
                        ->columns(2)
                        ->schema([
                            TextEntry::make('price')
                                ->label('Prix')
                                ->money('EUR'),
                            TextEntry::make('registrations_count')
                                ->label('Inscrits')
                                ->state(fn (Group $record): string => $record->registrations->count().' inscrits'),
                            RepeatableEntry::make('registrations')
                                ->hiddenLabel()
                                ->columnSpanFull()
                                ->table([
                                    TableColumn::make('Nom'),
                                    TableColumn::make('Adresse'),
                                    TableColumn::make('Téléphone ou gsm'),
                                    TableColumn::make('Email'),
                                    TableColumn::make('Né le'),
                                ])
                                ->schema([
                                ]),
                        ]),
                ]),
        ]);
    }
}
