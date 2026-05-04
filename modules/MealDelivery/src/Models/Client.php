<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Connection('maria-meal-delivery')]
#[Fillable([
    'salutation',
    'last_name',
    'first_name',
    'slug',
    'street',
    'number',
    'postal_code',
    'city',
    'floor',
    'email',
    'phone',
    'birth_date',
    'contact_name',
    'contact_phone',
    'contact_notes',
    'notes',
    'recurring_order',
    'route_id',
    'route_backup',
    'is_active',
    'use_cafeteria',
])]
final class Client extends Model
{
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'use_cafeteria' => 'boolean',
            'postal_code' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<DeliveryRoute, Client>
     */
    public function deliveryRoute(): BelongsTo
    {
        return $this->belongsTo(DeliveryRoute::class, 'route_id');
    }

    /**
     * @return HasOne<Absence, Client>
     */
    public function absence(): HasOne
    {
        return $this->hasOne(Absence::class);
    }

    /**
     * @return HasMany<Order, Client>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<RouteOrder, Client>
     */
    public function routeOrders(): HasMany
    {
        return $this->hasMany(RouteOrder::class);
    }

    /**
     * @return BelongsToMany<Diet, Client>
     */
    public function diets(): BelongsToMany
    {
        return $this->belongsToMany(Diet::class, 'client_diet');
    }

    /**
     * @return HasMany<Note, Client>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function __toString(): string
    {
        return $this->last_name.' '.$this->first_name;
    }
}
