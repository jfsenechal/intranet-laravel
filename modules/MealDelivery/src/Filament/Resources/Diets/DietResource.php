<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Diets;

use AcMarche\MealDelivery\Filament\Resources\Diets\Pages\CreateDiet;
use AcMarche\MealDelivery\Filament\Resources\Diets\Pages\EditDiet;
use AcMarche\MealDelivery\Filament\Resources\Diets\Pages\ListDiets;
use AcMarche\MealDelivery\Filament\Resources\Diets\Pages\ViewDiet;
use AcMarche\MealDelivery\Filament\Resources\Diets\Schemas\DietForm;
use AcMarche\MealDelivery\Filament\Resources\Diets\Schemas\DietInfoList;
use AcMarche\MealDelivery\Filament\Resources\Diets\Tables\DietTables;
use AcMarche\MealDelivery\Models\Diet;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class DietResource extends Resource
{
    #[Override]
    protected static ?string $model = Diet::class;

    #[Override]
    protected static ?int $navigationSort = 6;

    protected static string|UnitEnum|null $navigationGroup = 'Paramètres';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationLabel(): string
    {
        return 'Régimes alimentaires';
    }

    public static function form(Schema $schema): Schema
    {
        return DietForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DietInfoList::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DietTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiets::route('/'),
            'create' => CreateDiet::route('/create'),
            'edit' => EditDiet::route('/{record}/edit'),
            'view' => ViewDiet::route('/{record}/view'),
        ];
    }
}
