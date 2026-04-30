<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\BudgetArticles\Schemas;

use AcMarche\App\Enums\DepartmentEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class BudgetArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('department')
                            ->label('Département')
                            ->required()
                            ->options(DepartmentEnum::class)
                            ->enum(DepartmentEnum::class)
                            ->columnSpanFull(),
                        TextInput::make('functional_code')
                            ->label('Code fonctionnel')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('economic_code')
                            ->label('Code économique')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
