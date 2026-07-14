<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Deadlines\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class DeadlineInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Grid::make()
                    ->columnSpan(2)
                    ->columns(1)
                    ->schema([
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
                        Section::make('Dates')
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
                        Section::make('Note')
                            ->schema([
                                TextEntry::make('note')
                                    ->hiddenLabel()
                                    ->html()
                                    ->prose()
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Section::make('Métadonnées')
                    ->columnSpan(1)
                    ->columns(2)
                    ->schema([
                        IconEntry::make('is_closed')
                            ->label('Clôturé')
                            ->columnSpanFull()
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('user_add')
                            ->label('Par')
                            ->placeholder('—'),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),

                        TextEntry::make('updated_by')
                            ->label('Par')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
