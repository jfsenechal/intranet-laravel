<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Service;

use AcMarche\Mileage\Enums\TypeMovementEnum;
use AcMarche\Mileage\Models\PersonalInformation;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;

final class TripAttributeResolver
{
    public function setRate(Trip $trip): void
    {
        $rate = $this->resolveRate($trip);

        if ($rate instanceof Rate) {
            $trip->rate = $rate->amount;
            $trip->omnium = $this->isEntitledToOmnium($trip->user_add) ? $rate->omnium : 0;
        }
    }

    /**
     * Resolve the Rate applicable to the trip's departure date, if any.
     *
     * The comparison is made on the date alone: a trip departure is a datetime
     * while a rate period is bounded by dates, so a trip leaving at 08h30 on
     * the closing day of a period would otherwise fall past its end_date and
     * match no rate at all.
     */
    public function resolveRate(Trip $trip): ?Rate
    {
        if (! $trip->departure_date instanceof DateTimeInterface) {
            return null;
        }

        $departureDay = $trip->departure_date->format('Y-m-d');

        return Rate::query()
            ->whereDate('start_date', '<=', $departureDay)
            ->where(function ($query) use ($departureDay): void {
                $query->whereDate('end_date', '>=', $departureDay)
                    ->orWhereNull('end_date');
            })
            ->latest('start_date')
            ->first();
    }

    /**
     * Apply a rate to every trip within its period that is not yet linked to a
     * declaration. Declared trips are left untouched: their rate is a snapshot
     * of what was declared, and correcting those is the job of the
     * mileage:verify-trip-rates command.
     *
     * Takes two updates rather than one because the omnium is per beneficiary,
     * following the same entitlement gate as {@see self::setRate()}.
     *
     * @return int The number of trips updated.
     */
    public function applyRateToUndeclaredTrips(Rate $rate): int
    {
        $entitledUsernames = PersonalInformation::query()
            ->where('omnium', true)
            ->select('username');

        $entitled = $this->undeclaredTripsWithin($rate)
            ->whereIn('user_add', $entitledUsernames)
            ->update([
                'rate' => $rate->amount,
                'omnium' => $rate->omnium,
            ]);

        $notEntitled = $this->undeclaredTripsWithin($rate)
            ->whereNotIn('user_add', $entitledUsernames)
            ->update([
                'rate' => $rate->amount,
                'omnium' => 0,
            ]);

        return $entitled + $notEntitled;
    }

    public function setTypeOfMovement(Trip $trip): void
    {
        $isExternal = filled($trip->departure_location)
            && filled($trip->arrival_location)
            && $trip->arrival_date !== null;

        $trip->type_movement = $isExternal
            ? TypeMovementEnum::EXTERNAL->value
            : TypeMovementEnum::INTERNAL->value;
    }

    /**
     * Whether the beneficiary is entitled to the omnium share, which is
     * deducted from their mileage allowance. The entitlement lives on their
     * personal information; DeclarationFactory copies it onto the declaration,
     * and mileage:verify-trip-rates checks trips against that copy.
     *
     * A beneficiary without personal information is treated as not entitled,
     * matching the command's expectation of a zero omnium.
     */
    private function isEntitledToOmnium(?string $username): bool
    {
        if (blank($username)) {
            return false;
        }

        return (bool) PersonalInformation::query()
            ->where('username', $username)
            ->value('omnium');
    }

    /**
     * @return Builder<Trip>
     */
    private function undeclaredTripsWithin(Rate $rate): Builder
    {
        return Trip::query()
            ->where(function ($query): void {
                $query->whereNull('declaration_id')
                    ->orWhere('declaration_id', '<=', 0);
            })
            ->where('departure_date', '>=', $rate->start_date)
            ->when(
                $rate->end_date !== null,
                fn ($query) => $query->where('departure_date', '<=', $rate->end_date),
            );
    }
}
