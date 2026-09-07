<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Service;

use AcMarche\MealDelivery\Models\GuestReservation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Guest meals booked by the families of residents for one day. Feeds the kitchen
 * export of the home, which is kept apart from the meal delivery sheets: guests
 * eat on the spot, so they never appear on a route sheet or on the cafeteria one.
 */
final class DailyGuestsAggregator
{
    /**
     * @return array{
     *     date: CarbonImmutable,
     *     rows: list<array{
     *         resident_name: string,
     *         room: ?string,
     *         menu1: int,
     *         menu2: int,
     *         total: int,
     *         notes: ?string,
     *     }>,
     *     totals: array{residents: int, menu1: int, menu2: int, guests: int},
     * }
     */
    public function build(string $date): array
    {
        $dateCarbon = CarbonImmutable::parse($date);

        $reservations = GuestReservation::query()
            ->whereDate('date', $dateCarbon->format('Y-m-d'))
            ->with('resident')
            ->get()
            ->filter(fn (GuestReservation $reservation): bool => $reservation->resident !== null
                && $reservation->totalCount() > 0);

        $rows = $reservations
            ->sortBy(fn (GuestReservation $reservation): string => $reservation->resident->last_name)
            ->map(fn (GuestReservation $reservation): array => [
                'resident_name' => $reservation->resident->fullName(),
                'room' => self::room($reservation),
                'menu1' => (int) $reservation->menu1_count,
                'menu2' => (int) $reservation->menu2_count,
                'total' => $reservation->totalCount(),
                'notes' => self::notes($reservation),
            ])
            ->values()
            ->all();

        return [
            'date' => $dateCarbon,
            'rows' => $rows,
            'totals' => self::computeTotals($rows),
        ];
    }

    private static function room(GuestReservation $reservation): ?string
    {
        $room = mb_trim((string) ($reservation->resident->room ?? ''));

        return $room !== '' ? $room : null;
    }

    private static function notes(GuestReservation $reservation): ?string
    {
        $notes = mb_trim((string) ($reservation->notes ?? ''));

        return $notes !== '' ? $notes : null;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{residents: int, menu1: int, menu2: int, guests: int}
     */
    private static function computeTotals(array $rows): array
    {
        $collection = new Collection($rows);

        $menu1 = (int) $collection->sum('menu1');
        $menu2 = (int) $collection->sum('menu2');

        return [
            'residents' => $collection->count(),
            'menu1' => $menu1,
            'menu2' => $menu2,
            'guests' => $menu1 + $menu2,
        ];
    }
}
