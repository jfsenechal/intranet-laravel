<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Connection('maria-mediation')]
#[Fillable(['name', 'slug'])]
final class AgreementType extends Model
{
    public function caseFiles(): HasMany
    {
        return $this->hasMany(CaseFile::class);
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
