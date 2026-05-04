<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Connection('maria-mediation')]
#[Fillable([
    'number',
    'introduction_date',
    'closing_date',
    'nature',
    'description',
    'complainant_id',
    'agreement_type_id',
    'user_add',
])]
final class CaseFile extends Model
{
    public function complainant(): BelongsTo
    {
        return $this->belongsTo(Complainant::class);
    }

    public function agreementType(): BelongsTo
    {
        return $this->belongsTo(AgreementType::class);
    }

    protected function casts(): array
    {
        return [
            'introduction_date' => 'date',
            'closing_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
