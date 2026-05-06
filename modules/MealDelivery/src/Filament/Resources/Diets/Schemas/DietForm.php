<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Diets\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class DietForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                    ]),
            ]);
    }
}
