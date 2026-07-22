<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Meals\Schemas;

use AcMarche\MealDelivery\Service\ClientDietOptions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;

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

            CheckboxList::make('menu_1_diets')
                ->label('Régimes menu 1')
                ->options(fn (Get $get): array => self::clientDietOptions($get))
                ->in(fn (Get $get): array => self::acceptedDietIds($get))
                ->default([]),

            TextInput::make('menu_2')
                ->label('Menu 2')
                ->numeric()
                ->minValue(0)
                ->default(0),

            CheckboxList::make('menu_2_diets')
                ->label('Régimes menu 2')
                ->options(fn (Get $get): array => self::clientDietOptions($get))
                ->in(fn (Get $get): array => self::acceptedDietIds($get))
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

        return app(ClientDietOptions::class)->forClient((int) $clientId);
    }

    /**
     * The diets accepted by validation: the ones offered to the user, widened
     * with the ones the client's menus already carry. A diet unlinked from a
     * client after the fact must not make its existing orders unsavable.
     *
     * @return array<int, int>
     */
    private static function acceptedDietIds(Get $get): array
    {
        $clientId = $get('../../client_id');

        if (blank($clientId)) {
            return [];
        }

        return app(ClientDietOptions::class)->acceptedForClient((int) $clientId);
    }
}
