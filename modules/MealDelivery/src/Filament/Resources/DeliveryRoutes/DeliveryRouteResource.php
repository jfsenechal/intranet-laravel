<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes;

use AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\Pages\CreateDeliveryRoute;
use AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\Pages\EditDeliveryRoute;
use AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\Pages\ListDeliveryRoutes;
use AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\Schemas\DeliveryRouteForm;
use AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\Tables\DeliveryRouteTables;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class DeliveryRouteResource extends Resource
{
    #[Override]
    protected static ?string $model = DeliveryRoute::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'CPAS Repas';

    #[Override]
    protected static ?int $navigationSort = 5;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-map';
    }

    public static function getNavigationLabel(): string
    {
        return 'Delivery Routes';
    }

    public static function form(Schema $schema): Schema
    {
        return DeliveryRouteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryRouteTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryRoutes::route('/'),
            'create' => CreateDeliveryRoute::route('/create'),
            'edit' => EditDeliveryRoute::route('/{record}/edit'),
        ];
    }
}
