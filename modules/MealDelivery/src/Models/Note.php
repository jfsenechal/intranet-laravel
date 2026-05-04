<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Connection('maria-meal-delivery')]
#[Fillable(['client_id', 'note_date', 'description', 'is_done', 'done_by'])]
final class Note extends Model
{
    protected function casts(): array
    {
        return [
            'note_date' => 'date',
            'is_done' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Client, Note>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function __toString(): string
    {
        return 'Note of '.($this->note_date?->format('d-m-Y') ?? '');
    }
}
