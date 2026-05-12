<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Connection('maria-meal-delivery')]
#[Fillable(['name'])]
#[Table(name: 'delivery_routes')]
final class DeliveryRoute extends Model
{
    public function __toString(): string
    {
        return (string) $this->name;
    }

    /**
     * @return HasMany<Client, DeliveryRoute>
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'route_id');
    }

    /**
     * Active clients not using cafeteria
     *
     * @return HasMany<Client, DeliveryRoute>
     */
    public function activeClients(): HasMany
    {
        return $this->hasMany(Client::class, 'route_id')
            ->where('use_cafeteria', false)
            ->where('is_active', true)
            ->orderByRaw('route_position ASC')
            ->orderBy('last_name');
    }
}
