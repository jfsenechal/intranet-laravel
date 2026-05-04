<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Connection('maria-cpasrepas')]
#[Fillable(['first_day', 'days', 'notes', 'is_archived'])]
final class Week extends Model
{
    protected function casts(): array
    {
        return [
            'first_day' => 'date',
            'days' => 'array',
            'is_archived' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Order, Week>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function __toString(): string
    {
        return 'Week of: '.($this->first_day?->format('d-m-Y') ?? '');
    }
}
