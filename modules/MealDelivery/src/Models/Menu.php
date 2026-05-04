<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Connection('maria-meal-delivery')]
#[Fillable(['position', 'quantity', 'meal_id'])]
final class Menu extends Model
{
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'quantity' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Meal, Menu>
     */
    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    /**
     * @return BelongsToMany<Diet, Menu>
     */
    public function diets(): BelongsToMany
    {
        return $this->belongsToMany(Diet::class, 'diet_menu');
    }
}
