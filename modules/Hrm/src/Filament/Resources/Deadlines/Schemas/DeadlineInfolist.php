<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Deadlines\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class DeadlineInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Agent et employeur')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('employee.last_name')
                            ->label('Agent')
                            ->formatStateUsing(
                                fn ($record): string => $record->employee?->last_name.' '.$record->employee?->first_name
                            ),
                        TextEntry::make('employer.name')
                            ->label('Employeur'),
                        TextEntry::make('direction.name')
                            ->label('Direction'),
                        TextEntry::make('service.name')
                            ->label('Service'),
                    ]),
                Section::make('Détails')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Intitulé'),
                        TextEntry::make('note')
                            ->label('Note')
                            ->html()
                            ->prose()
                            ->columnSpanFull(),
                    ]),
                Fieldset::make('Dates')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('start_date')
                            ->label('Date de début')
                            ->date('d/m/Y'),
                        TextEntry::make('end_date')
                            ->label('Date de fin')
                            ->date('d/m/Y'),
                        TextEntry::make('reminder_date')
                            ->label('Date de rappel')
                            ->date('d/m/Y'),
                        TextEntry::make('closed_date')
                            ->label('Date de clôture')
                            ->date('d/m/Y'),
                    ]),
                Fieldset::make('Options')
                    ->schema([
                        IconEntry::make('is_closed')
                            ->label('Clôturée')
                            ->boolean(),
                    ]),
            ]);
    }
}
