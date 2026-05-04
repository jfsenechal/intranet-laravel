<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Connection('maria-meal-delivery')]
#[Fillable(['name'])]
final class DeliveryRoute extends Model
{
    protected $table = 'delivery_routes';

    /**
     * @return HasMany<Client, DeliveryRoute>
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'route_id');
    }

    /**
     * @return HasMany<RouteOrder, DeliveryRoute>
     */
    public function routeOrders(): HasMany
    {
        return $this->hasMany(RouteOrder::class, 'route_id');
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
