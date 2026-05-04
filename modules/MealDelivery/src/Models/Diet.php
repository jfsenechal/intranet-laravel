<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Connection('maria-cpasrepas')]
#[Fillable(['name', 'not_deletable'])]
final class Diet extends Model
{
    protected function casts(): array
    {
        return [
            'not_deletable' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<Client, Diet>
     */
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_diet');
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
