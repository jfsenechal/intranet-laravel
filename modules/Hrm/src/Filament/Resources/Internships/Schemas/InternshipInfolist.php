<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Internships\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class InternshipInfolist
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
                        Section::make('Affectation')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('employer.name')
                                    ->label('Employeur')
                                    ->placeholder('—'),
                                TextEntry::make('direction.name')
                                    ->label('Direction')
                                    ->placeholder('—'),
                                TextEntry::make('service.name')
                                    ->label('Service')
                                    ->placeholder('—'),
                            ]),
                        Fieldset::make('Dates')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('start_date')
                                    ->label('Date de début')
                                    ->date('d/m/Y'),
                                TextEntry::make('end_date')
                                    ->label('Date de fin')
                                    ->date('d/m/Y'),
                                TextEntry::make('reminder_date')
                                    ->label('Date de rappel')
                                    ->date('d/m/Y')
                                    ->placeholder('—'),
                            ]),
                        Section::make('Notes')
                            ->schema([
                                TextEntry::make('notes')
                                    ->label('Notes')
                                    ->hiddenLabel()
                                    ->html()
                                    ->prose()
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Section::make('Suivi')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('user_add')
                            ->label('Créé par'),
                        TextEntry::make('updated_by')
                            ->label('Modifié par')
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
