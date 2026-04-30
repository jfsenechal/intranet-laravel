<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Deadlines\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class DeadlineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Flex::make([
                    TextInput::make('name')
                        ->label('Intitulé')
                        ->required()
                        ->maxLength(250),
                    Toggle::make('is_closed')
                        ->label('Clôturée'),
                ]),
                Section::make('Agent et employeur')
                    ->columns(2)
                    ->schema([
                        Select::make('employee_id')
                            ->label('Agent')
                            ->relationship('employee', 'last_name')
                            ->getOptionLabelFromRecordUsing(
                                fn ($record): string => $record->last_name.' '.$record->first_name
                            )
                            ->searchable()
                            ->preload(),
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
                Section::make('Dates')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Date de début'),
                        DatePicker::make('end_date')
                            ->label('Date de fin'),
                        DatePicker::make('reminder_date')
                            ->label('Date de rappel'),
                        DatePicker::make('closed_date')
                            ->label('Date de clôture'),
                    ]),
                RichEditor::make('note')
                    ->label('Note')
                    ->columnSpanFull(),
            ]);
    }
}
