<?php

namespace AcMarche\Mileage\Filament\Resources\BudgetArticleResource\Schema;

use AcMarche\Mileage\Enums\DepartmentEnum;
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
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('department')
                            ->label('Département')
                            ->required()
                            ->options(DepartmentEnum::class)
                            ->enum(DepartmentEnum::class)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('functional_code')
                            ->label('Code fonctionnel')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('economic_code')
                            ->label('Code économique')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
