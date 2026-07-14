<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Orders;

use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\CreateOrder;
use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\EditOrder;
use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\ViewOrder;
use AcMarche\MealDelivery\Filament\Resources\Orders\Schemas\OrderForm;
use AcMarche\MealDelivery\Filament\Resources\Orders\Schemas\OrderInfolist;
use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Models\Order;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Override;

final class OrderResource extends Resource
{
    #[Override]
    protected static ?string $model = Order::class;

    /**
     * Orders are always reached through a client or a week, never listed on their own.
     */
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
            'view' => ViewOrder::route('/{record}/view'),
        ];
    }

    /**
     * Orders have no index page; send breadcrumbs and default routing to the weeks list.
     */
    #[Override]
    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        return WeekResource::getUrl('index', [], $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
    }
}
