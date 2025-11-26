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
                        Forms\Components\TextInput::make('montant')
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

                        Forms\Components\DatePicker::make('date_debut')
                            ->label('Date début')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\DatePicker::make('date_fin')
                            ->label('Date fin')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->after('date_debut'),
                    ])
                    ->columns(2),
            ]);
    }
}
