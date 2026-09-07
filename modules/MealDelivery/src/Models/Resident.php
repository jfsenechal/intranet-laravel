<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A resident of the home. Residents are not clients of the meal delivery service:
 * their own meals are part of the home's catering and are never encoded nor billed
 * here. Only the extra meals their family books are.
 *
 * @property string|null $user_add
 */
#[Connection('maria-meal-delivery')]
#[Fillable([
    'last_name',
    'first_name',
    'room',
    'is_active',
    'notes',
])]
final class Resident extends Model
{
    use HasUserAdd;

    public function __toString(): string
    {
        return $this->fullName();
    }

    public function fullName(): string
    {
        return mb_trim($this->last_name.' '.($this->first_name ?? ''));
    }

    /**
     * @return HasMany<GuestReservation, Resident>
     */
    public function guestReservations(): HasMany
    {
        return $this->hasMany(GuestReservation::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();

        self::deleting(function (Resident $resident): void {
            $resident->guestReservations->each(
                fn (GuestReservation $reservation): ?bool => $reservation->delete(),
            );
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
