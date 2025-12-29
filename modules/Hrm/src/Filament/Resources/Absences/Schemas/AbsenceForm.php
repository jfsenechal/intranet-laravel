<?php

namespace AcMarche\Hrm\Filament\Resources\Absences\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class AbsenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Agent')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Agent')
                            ->relationship('employee', 'last_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->last_name.' '.$record->first_name)
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                Fieldset::make('Periode')
                    ->columns(4)
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Date de debut'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Date de fin'),
                        Forms\Components\DatePicker::make('reminder_date')
                            ->label('Date de rappel'),
                        Forms\Components\DatePicker::make('closed_date')
                            ->label('Date de cloture'),
                    ]),
                Fieldset::make('Motif')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('reason')
                            ->label('Raison')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('ssa')
                            ->label('SSA')
                            ->maxLength(5),
                    ]),
                Fieldset::make('Options')
                    ->columns(4)
                    ->schema([
                        Forms\Components\Select::make('has_resumed')
                            ->label('Reprise')
                            ->options([
                                'oui' => 'Oui',
                                'non' => 'Non',
                            ]),
                        Forms\Components\Select::make('clock_updated')
                            ->label('Pointeuse')
                            ->options([
                                'oui' => 'Oui',
                                'non' => 'Non',
                            ]),
                        Forms\Components\Select::make('acropole')
                            ->label('Acropole')
                            ->options([
                                'oui' => 'Oui',
                                'non' => 'Non',
                            ]),
                        Forms\Components\Select::make('agent_file')
                            ->label('Dossier agent')
                            ->options([
                                'oui' => 'Oui',
                                'non' => 'Non',
                            ]),
                        Forms\Components\Toggle::make('is_closed')
                            ->label('Cloture'),
                    ]),
                Section::make('Notes')
                    ->schema([
                        Forms\Components\RichEditor::make('notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
