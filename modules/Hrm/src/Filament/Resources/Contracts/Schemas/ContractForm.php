<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Contracts\Schemas;

use AcMarche\Hrm\Enums\ContractStatusEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Agent et employeur')
                    ->columns(2)
                    ->schema([
                        Select::make('employer_id')
                            ->label('Employeur')
                            ->relationship('employer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
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
                        Select::make('status')
                            ->label('Statut')
                            ->options(ContractStatusEnum::class)
                            ->enum(ContractStatusEnum::class),
                    ]),
                Section::make('Details du contrat')
                    ->columns(3)
                    ->schema([
                        Select::make('contract_type_id')
                            ->label('Type de contrat')
                            ->relationship('contractType', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('contract_nature_id')
                            ->label('Nature du contrat')
                            ->relationship('contractNature', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('pay_scale_id')
                            ->label('Echelle')
                            ->relationship('payScale', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('job_title')
                            ->label('Fonction')
                            ->maxLength(250),
                        TextInput::make('work_regime')
                            ->label('Regime de travail (ETP)')
                            ->helperText(
                                'Par exemple 0,50 - Si interruption de carrière à 4/5 : Régime horaire = 38/38 et Régime ETP = 0,80'
                            )
                            ->numeric(),
                        TextInput::make('hourly_regime')
                            ->label('Regime horaire')
                            ->helperText('Par exemple 19/38')
                            ->maxLength(255),
                    ]),
                Section::make('Dates')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Date de debut'),
                        DatePicker::make('end_date')
                            ->label('Date de fin'),
                        DatePicker::make('reminder_date')
                            ->label('Date de rappel'),
                    ]),
                Section::make('Remplacement')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_replacement')
                            ->label('Remplacement'),
                        Select::make('replaces_id')
                            ->label('Remplace')
                            ->relationship('replaces', 'last_name')
                            ->getOptionLabelFromRecordUsing(
                                fn ($record): string => $record->last_name.' '.$record->first_name
                            )
                            ->searchable()
                            ->preload(),
                    ]),
                Section::make('Options')
                    ->columns(4)
                    ->schema([
                        Toggle::make('is_closed')
                            ->label('Cloture'),
                        Toggle::make('is_amendment')
                            ->label('Avenant'),
                        Toggle::make('is_suspended')
                            ->label('Suspension')
                            ->helperText('Interruption de carrière, congé parental, congé sans solde,...'),
                    ]),
                Section::make('Documents')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('file1_name')
                            ->label('Document 1')
                            ->disk('public')
                            ->directory(config('hrm.uploads.contracts')),
                        FileUpload::make('file2_name')
                            ->label('Document 2')
                            ->disk('public')
                            ->directory(config('hrm.uploads.contracts')),
                    ]),
                RichEditor::make('college')
                    ->label('College')
                    ->columnSpanFull(),
            ]);
    }
}
