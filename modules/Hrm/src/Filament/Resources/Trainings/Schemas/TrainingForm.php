<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Trainings\Schemas;

use AcMarche\Hrm\Enums\ListOptions;
use AcMarche\Hrm\Enums\TrainingTypeEnum;
use AcMarche\Hrm\Models\Training;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

final class TrainingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Formation')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Intitulé')
                            ->required()
                            ->maxLength(150),
                        Select::make('training_type')
                            ->label('Type de formation')
                            ->options(TrainingTypeEnum::class)
                            ->enum(TrainingTypeEnum::class)
                            ->required(),
                        Fieldset::make('Durée')
                            ->columns(2)
                            ->schema([
                                TextInput::make('duration_hours')
                                    ->label('Heures')
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix('h')
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function (Set $set, ?Training $record): void {
                                        $set('duration_hours', intdiv((int) $record?->duration_minutes, 60));
                                    })
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set): void {
                                        $set('duration_minutes', ((int) $get('duration_hours')) * 60 + ((int) $get('duration_minutes_part')));
                                    }),
                                TextInput::make('duration_minutes_part')
                                    ->label('Minutes')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(59)
                                    ->suffix('min')
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function (Set $set, ?Training $record): void {
                                        $set('duration_minutes_part', ((int) $record?->duration_minutes) % 60);
                                    })
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set): void {
                                        $set('duration_minutes', ((int) $get('duration_hours')) * 60 + ((int) $get('duration_minutes_part')));
                                    }),
                                Hidden::make('duration_minutes'),
                            ]),
                    ]),
                Fieldset::make('Dates')
                    ->columns(4)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Date de début'),
                        DatePicker::make('end_date')
                            ->label('Date de fin'),
                        DatePicker::make('college_date')
                            ->label('Date de Collège'),
                        DatePicker::make('reminder_date')
                            ->label('Date de rappel'),
                    ]),
                Fieldset::make('Accord')
                    ->columns(2)
                    ->schema([
                        Select::make('granted_by')
                            ->label('Accorde par')
                            ->options(ListOptions::getAccordePar()),
                        DatePicker::make('granted_at')
                            ->label('Accordé le'),
                    ]),
                Fieldset::make('Attestation')
                    ->columns(3)
                    ->schema([
                        Toggle::make('certificate_received')
                            ->label('Attestation recue'),
                        DatePicker::make('certificate_received_at')
                            ->label('Recue le'),
                        FileUpload::make('certificate_file')
                            ->label('Fichier attestation')
                            ->disk('public')
                            ->directory(config('hrm.uploads.formations'))
                            ->columnSpanFull(),
                    ]),
                Section::make('Description')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Description')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),
                Toggle::make('is_closed')
                    ->label('Clôturée'),
            ]);
    }
}
