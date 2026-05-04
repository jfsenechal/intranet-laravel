<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Connection('maria-offenses')]
#[Fillable([
    'slug',
    'last_name',
    'first_name',
    'birth_date',
    'street',
    'postal_code',
    'city',
    'user_add',
])]
final class Offender extends Model
{
    public function offenses(): HasMany
    {
        return $this->hasMany(Offense::class);
    }

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
