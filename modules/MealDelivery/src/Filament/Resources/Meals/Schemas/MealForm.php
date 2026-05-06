<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Meals\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class MealForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema(),
            ]);
    }

    public static function getMealSchema(): array
    {
        return [
            Hidden::make('date'),

            TextInput::make('soup_count')
                ->label('Potage')
                ->numeric()
                ->minValue(0)
                ->default(0),

            TextInput::make('menu_1')
                ->label('Menu 1')
                ->numeric()
                ->minValue(0)
                ->default(0),

            TextInput::make('menu_2')
                ->label('Menu 2')
                ->numeric()
                ->minValue(0)
                ->default(0),

            Textarea::make('notes')
                ->label('Remarque')
                ->rows(2)
                ->nullable(),
        ];
    }
}
