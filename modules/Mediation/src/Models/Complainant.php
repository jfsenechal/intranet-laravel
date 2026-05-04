<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Connection('maria-mediation')]
#[Fillable([
    'slug',
    'salutation',
    'last_name',
    'first_name',
    'birth_date',
    'street',
    'postal_code',
    'city',
    'user_add',
])]
final class Complainant extends Model
{
    public function caseFiles(): HasMany
    {
        return $this->hasMany(CaseFile::class);
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
