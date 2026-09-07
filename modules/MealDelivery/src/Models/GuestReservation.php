<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Extra meals booked by the family of a resident for a given day. Guests always
 * eat at midday at the home, so only the two menu positions are recorded. The
 * resident's own meal is not part of this: it is covered by the home's catering
 * and never billed. Only the guests pay.
 *
 * @property string|null $user_add
 */
#[Connection('maria-meal-delivery')]
#[Fillable([
    'resident_id',
    'date',
    'menu1_count',
    'menu2_count',
    'notes',
])]
final class GuestReservation extends Model
{
    use HasUserAdd;

    public function __toString(): string
    {
        return $this->date?->format('d-m-Y') ?? '';
    }

    /**
     * Number of guests expected, all menus taken together.
     */
    public function totalCount(): int
    {
        return (int) $this->menu1_count + (int) $this->menu2_count;
    }

    /**
     * @return BelongsTo<Resident, GuestReservation>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'menu1_count' => 'integer',
            'menu2_count' => 'integer',
        ];
    }
}
