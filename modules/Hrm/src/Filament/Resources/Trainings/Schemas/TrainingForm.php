<?php

namespace AcMarche\Hrm\Filament\Resources\Trainings\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class TrainingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Agent et formation')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Agent')
                            ->relationship('employee', 'last_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->last_name.' '.$record->first_name)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label('Intitule')
                            ->required()
                            ->maxLength(150),
                        Forms\Components\Select::make('training_type')
                            ->label('Type de formation')
                            ->options([
                                'type1' => 'Type 1',
                                'type2' => 'Type 2',
                                'type3' => 'Type 3',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('duration_hours')
                            ->label('Duree (heures)')
                            ->numeric()
                            ->suffix('heures'),
                    ]),
                Fieldset::make('Dates')
                    ->columns(4)
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Date de debut'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Date de fin'),
                        Forms\Components\DatePicker::make('college_date')
                            ->label('Date college'),
                        Forms\Components\DatePicker::make('reminder_date')
                            ->label('Date de rappel'),
                    ]),
                Fieldset::make('Accord')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('granted_by')
                            ->label('Accorde par')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('granted_at')
                            ->label('Accorde le'),
                    ]),
                Fieldset::make('Attestation')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Toggle::make('certificate_received')
                            ->label('Attestation recue'),
                        Forms\Components\DatePicker::make('certificate_received_at')
                            ->label('Recue le'),
                        Forms\Components\FileUpload::make('certificate_file')
                            ->label('Fichier attestation')
                            ->disk('public')
                            ->directory('uploads/hrm/formations'),
                    ]),
                Section::make('Description')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Toggle::make('is_closed')
                    ->label('Cloture'),
            ]);
    }
}
