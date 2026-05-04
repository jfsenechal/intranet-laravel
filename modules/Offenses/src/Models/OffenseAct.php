<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Connection('maria-offenses')]
#[Fillable(['name', 'user_add'])]
final class OffenseAct extends Model
{
    public function offenses(): HasMany
    {
        return $this->hasMany(Offense::class);
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
