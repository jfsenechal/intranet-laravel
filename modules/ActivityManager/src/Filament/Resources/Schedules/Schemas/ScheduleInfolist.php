<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Schedules\Schemas;

use AcMarche\ActivityManager\Models\Schedule;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class ScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
                    ->icon(Heroicon::AcademicCap)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('activity.name')
                            ->label('Activité')
                            ->icon(Heroicon::Tag)
                            ->badge()
                            ->color('primary')
                            ->placeholder('—'),
                        TextEntry::make('members_count')
                            ->label('Inscrits')
                            ->icon(Heroicon::UserGroup)
                            ->badge()
                            ->color('success')
                            ->state(fn (Schedule $record): int => $record->members()->count()),
                    ]),
                Section::make('Période')
                    ->icon(Heroicon::CalendarDays)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('start_date')
                            ->label('Début')
                            ->icon(Heroicon::CalendarDays)
                            ->date('d/m/Y'),
                        TextEntry::make('end_date')
                            ->label('Fin')
                            ->icon(Heroicon::CalendarDays)
                            ->date('d/m/Y')
                            ->placeholder('En cours'),
                    ]),
            ]);
    }
}
