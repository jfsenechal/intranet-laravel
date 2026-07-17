<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Meals\Schemas;

use AcMarche\MealDelivery\Models\Diet;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;

final class MealForm
{
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

            Select::make('menu_1_diets')
                ->label('Régimes menu 1')
                ->multiple()
                ->options(fn(Get $get): array => self::clientDietOptions($get))
                ->default([]),

            TextInput::make('menu_2')
                ->label('Menu 2')
                ->numeric()
                ->minValue(0)
                ->default(0),

            Select::make('menu_2_diets')
                ->label('Régimes menu 2')
                ->multiple()
                ->options(fn(Get $get): array => self::clientDietOptions($get))
                ->default([]),

            Toggle::make('at_cafeteria')
                ->label('Cafeteria')
                ->default(false),

            Textarea::make('notes')
                ->label('Remarque')
                ->rows(2)
                ->nullable(),
        ];
    }

    /**
     * The diets of the client the order belongs to, keyed by diet id.
     *
     * @return array<int, string>
     */
    private static function clientDietOptions(Get $get): array
    {
        $clientId = $get('../../client_id');

        if (blank($clientId)) {
            return [];
        }

        return Diet::query()
            ->whereHas('clients', fn(Builder $query) => $query->whereKey($clientId))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
