<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Meals;

use AcMarche\MealDelivery\Filament\Resources\Meals\Pages\CreateMeal;
use AcMarche\MealDelivery\Filament\Resources\Meals\Pages\EditMeal;
use AcMarche\MealDelivery\Filament\Resources\Meals\Pages\ListMeals;
use AcMarche\MealDelivery\Filament\Resources\Meals\Schemas\MealForm;
use AcMarche\MealDelivery\Filament\Resources\Meals\Tables\MealTables;
use AcMarche\MealDelivery\Models\Meal;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class MealResource extends Resource
{
    #[Override]
    protected static ?string $model = Meal::class;

    #[Override]
    protected static ?int $navigationSort = 4;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cake';
    }

    public static function getNavigationLabel(): string
    {
        return 'Meals';
    }

    public static function form(Schema $schema): Schema
    {
        return MealForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MealTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeals::route('/'),
            'create' => CreateMeal::route('/create'),
            'edit' => EditMeal::route('/{record}/edit'),
        ];
    }
}
