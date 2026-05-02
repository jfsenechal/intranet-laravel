<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Connection('maria-cpasrepas')]
#[Fillable(['client_id', 'start_date', 'end_date'])]
final class Absence extends Model
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Client, Absence>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
