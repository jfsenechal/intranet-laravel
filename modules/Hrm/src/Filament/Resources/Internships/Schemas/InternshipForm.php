<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Internships\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class InternshipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Affectation')
                    ->columns(3)
                    ->schema([
                        Select::make('employer_id')
                            ->label('Employeur')
                            ->relationship('employer', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('direction_id')
                            ->label('Direction')
                            ->relationship('direction', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('service_id')
                            ->label('Service')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
                Fieldset::make('Dates')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Date de début')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('end_date')
                            ->label('Date de fin')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual('start_date'),
                        DatePicker::make('reminder_date')
                            ->label('Date de rappel')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ]),
                Section::make('Notes')
                    ->schema([
                        RichEditor::make('notes')
                            ->label('Notes')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
