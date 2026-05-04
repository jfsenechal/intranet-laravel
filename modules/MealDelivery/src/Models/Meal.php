<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Connection('maria-meal-delivery')]
#[Fillable(['date', 'soup_count', 'notes', 'order_id', 'at_cafeteria'])]
final class Meal extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'soup_count' => 'integer',
            'at_cafeteria' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Order, Meal>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return HasMany<Menu, Meal>
     */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function __toString(): string
    {
        return $this->date?->format('d-m-Y') ?? '';
    }
}
