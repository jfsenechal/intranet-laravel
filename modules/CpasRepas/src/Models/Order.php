<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Connection('maria-cpasrepas')]
#[Fillable(['week_id', 'client_id', 'notes', 'is_last_meal'])]
final class Order extends Model
{
    protected function casts(): array
    {
        return [
            'is_last_meal' => 'boolean',
        ];
    }

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
}
