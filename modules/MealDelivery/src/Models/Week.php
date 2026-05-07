<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Connection('maria-meal-delivery')]
#[Fillable(['first_day', 'days', 'notes', 'is_archived'])]
final class Week extends Model
{
    public function __toString(): string
    {
        return 'Week of: '.($this->first_day?->format('d-m-Y') ?? '');
    }

    public function formattedFirstDay(): string
    {
        return $this->first_day?->translatedFormat('j F Y') ?? '';
    }

    /**
     * @return HasMany<Order, Week>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected function casts(): array
    {
        return [
            'first_day' => 'date',
            'days' => 'array',
            'is_archived' => 'boolean',
        ];
    }
}
