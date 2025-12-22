<?php

namespace AcMarche\Mileage\Handler;

use AcMarche\Mileage\Models\Rate;

class RateHandler
{
    public static function hasOverlappingRate(string $startDate, string $endDate, ?Rate $currentRecord): bool
    {
        $query = Rate::query()
            ->where(function ($query) use ($startDate, $endDate): void {
                // Check if new range overlaps with existing ranges
                // Overlap exists if: new_start <= existing_end AND new_end >= existing_start
                $query->where('start_date', '<=', $endDate)
                    ->where(function ($q) use ($startDate): void {
                        $q->where('end_date', '>=', $startDate)
                            ->orWhereNull('end_date');
                    });
            });

        // Ignore current record when editing
        if ($currentRecord?->exists) {
            $query->where('id', '!=', $currentRecord->id);
        }

        return $query->exists();
    }
}
