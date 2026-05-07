<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Orders;

use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\CreateOrder;
use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\EditOrder;
use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\ListOrders;
use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\ViewOrder;
use AcMarche\MealDelivery\Filament\Resources\Orders\Schemas\OrderForm;
use AcMarche\MealDelivery\Filament\Resources\Orders\Schemas\OrderInfolist;
use AcMarche\MealDelivery\Filament\Resources\Orders\Tables\OrderTables;
use AcMarche\MealDelivery\Models\Order;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class OrderResource extends Resource
{
    #[Override]
    protected static ?string $model = Order::class;

    #[Override]
    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clipboard-document-list';
    }

    public static function getNavigationLabel(): string
    {
        return 'Commandes';
    }

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
            'view' => ViewOrder::route('/{record}/view'),
        ];
    }
}
