<?php

namespace AcMarche\Mileage\Filament\Resources\BudgetArticleResource\Schema;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BudgetArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('departement')
                            ->label('Département')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('fonctionnel')
                            ->label('Code fonctionnel')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('economique')
                            ->label('Code économique')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
