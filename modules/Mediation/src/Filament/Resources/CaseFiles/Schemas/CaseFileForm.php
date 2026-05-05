<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\CaseFiles\Schemas;

use AcMarche\Mediation\Models\Complainant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CaseFileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Dossier')
                    ->schema([
                        TextInput::make('number')
                            ->label('Numéro')
                            ->numeric()
                            ->nullable(),

                        TextInput::make('nature')
                            ->label('Nature')
                            ->required()
                            ->maxLength(255),

                        DatePicker::make('introduction_date')
                            ->label("Date d'introduction")
                            ->required(),

                        DatePicker::make('closing_date')
                            ->label('Date de clôture')
                            ->nullable(),

                        Textarea::make('description')
                            ->label('Description')
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Relations')
                    ->schema([
                        Select::make('complainant_id')
                            ->label('Plaignant')
                            ->relationship('complainant', 'last_name')
                            ->getOptionLabelFromRecordUsing(
                                fn(Complainant $record) => $record->last_name.' '.$record->first_name
                            )
                            ->searchable()
                            ->required()
                            ->disabled(fn($get) => filled($get('complainant_id'))),
                        Hidden::make('complainant_id')
                            ->visible(fn($get) => filled($get('complainant_id'))),
                        Select::make('agreement_type_id')
                            ->label("Type d'accord")
                            ->relationship('agreementType', 'name')
                            ->searchable()
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }
}
