<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Connection('maria-meal-delivery')]
#[Fillable(['week_id', 'client_id', 'notes', 'is_last_meal'])]
final class Order extends Model
{
    /**
     * @return BelongsTo<Week, Order>
     */
    public function week(): BelongsTo
    {
        return $this->belongsTo(Week::class);
    }

    /**
     * @return BelongsTo<Client, Order>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return HasMany<Meal, Order>
     */
    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class);
    }

    protected static function booted(): void
    {
        self::deleting(function (Order $order): void {
            $order->meals->each(fn (Meal $meal): ?bool => $meal->delete());
        });
    }

    protected function casts(): array
    {
        return [
            'is_last_meal' => 'boolean',
        ];
    }
}
