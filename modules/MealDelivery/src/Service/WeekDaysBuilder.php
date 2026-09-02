<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Service;

use Carbon\CarbonImmutable;
use DateTimeInterface;

final class WeekDaysBuilder
{
    public const int DAYS_PER_WEEK = 7;

    /**
     * Monday to Sunday of the week the given day belongs to.
     *
     * @return list<string>
     */
    public function fullWeek(DateTimeInterface|string $firstDay): array
    {
        $start = CarbonImmutable::parse($firstDay)->startOfWeek();

        return collect(range(0, self::DAYS_PER_WEEK - 1))
            ->map(fn (int $offset): string => $start->addDays($offset)->format('Y-m-d'))
            ->all();
    }

    /**
     * Add the missing days of the Monday-Sunday range without dropping any day
     * already stored: a week may legitimately carry a date outside that range.
     *
     * @param  array<int, mixed>  $days
     * @return list<string>
     */
    public function complete(array $days, DateTimeInterface|string $firstDay): array
    {
        return $this->normalize([...$days, ...$this->fullWeek($firstDay)]);
    }

    /**
     * @param  array<int, mixed>  $days
     * @return list<string>
     */
    public function normalize(array $days): array
    {
        return collect($days)
            ->filter()
            ->map(fn ($day): string => CarbonImmutable::parse((string) $day)->format('Y-m-d'))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
