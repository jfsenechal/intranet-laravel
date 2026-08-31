<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $user_add
 */
#[Connection('maria-meal-delivery')]
#[Fillable(['client_id', 'note_date', 'description', 'is_done'])]
final class Note extends Model
{
    use HasUserAdd;

    public $timestamps = false;

    public function __toString(): string
    {
        return 'Note of '.($this->note_date?->format('d-m-Y') ?? '');
    }

    /**
     * @return BelongsTo<Client, Note>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }

    protected function casts(): array
    {
        return [
            'note_date' => 'date',
            'is_done' => 'boolean',
        ];
    }
}
