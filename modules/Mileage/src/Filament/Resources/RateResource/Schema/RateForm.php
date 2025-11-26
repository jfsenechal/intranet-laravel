<?php

namespace AcMarche\Mileage\Filament\Resources\RateResource\Schema;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Montant (€/km)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        Forms\Components\TextInput::make('omnium')
                            ->label('Omnium (€/km)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Date début')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Date fin')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->after('start_date'),
                    ])
                    ->columns(2),
            ]);
    }
}
