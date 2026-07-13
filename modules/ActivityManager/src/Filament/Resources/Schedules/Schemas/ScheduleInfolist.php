<?php

namespace AcMarche\ActivityManager\Filament\Resources\Schedules\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScheduleInfolist
{
    public static  function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('activity.name')
                            ->label('Activité')
                            ->badge()
                            ->placeholder('—'),
                        TextEntry::make('start_date')
                            ->label('Début')
                            ->date('d/m/Y'),
                        TextEntry::make('end_date')
                            ->label('Fin')
                            ->date('d/m/Y')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
