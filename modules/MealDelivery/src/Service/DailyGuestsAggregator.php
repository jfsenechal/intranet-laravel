<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Service;

use AcMarche\MealDelivery\Models\GuestReservation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Guest meals booked by cafeteria clients for one day. Feeds the "Invités" block
 * of the cafeteria sheet, which used to be handwritten at the bottom of the
 * printout before being handed to the kitchen.
 */
final class DailyGuestsAggregator
{
    /**
     * @return array{
     *     date: CarbonImmutable,
     *     rows: list<array{
     *         client_name: string,
     *         address_line: string,
     *         menu1: int,
     *         menu2: int,
     *         total: int,
     *         notes: ?string,
     *     }>,
     *     totals: array{clients: int, menu1: int, menu2: int, guests: int},
     * }
     */
    public function build(string $date): array
    {
        $dateCarbon = CarbonImmutable::parse($date);

        $reservations = GuestReservation::query()
            ->whereDate('date', $dateCarbon->format('Y-m-d'))
            ->with('client')
            ->get()
            ->filter(fn (GuestReservation $reservation): bool => $reservation->client !== null
                && $reservation->totalCount() > 0);

        $rows = $reservations
            ->sortBy(fn (GuestReservation $reservation): string => $reservation->client->last_name)
            ->map(fn (GuestReservation $reservation): array => [
                'client_name' => mb_trim(
                    $reservation->client->last_name.' '.$reservation->client->first_name,
                ),
                'address_line' => self::addressLine($reservation),
                'menu1' => (int) $reservation->menu1_count,
                'menu2' => (int) $reservation->menu2_count,
                'total' => $reservation->totalCount(),
                'notes' => $reservation->notes !== null && mb_trim((string) $reservation->notes) !== ''
                    ? (string) $reservation->notes
                    : null,
            ])
            ->values()
            ->all();

        return [
            'date' => $dateCarbon,
            'rows' => $rows,
            'totals' => self::computeTotals($rows),
        ];
    }

    /**
     * Street, number and floor, mirroring the address shown on the client rows of
     * the same sheet.
     */
    private static function addressLine(GuestReservation $reservation): string
    {
        $client = $reservation->client;

        $addressLine = mb_trim($client->street.' '.$client->number);
        $floor = mb_trim((string) ($client->floor ?? ''));

        if ($floor !== '') {
            $addressLine .= ' '.$floor;
        }

        return $addressLine;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{clients: int, menu1: int, menu2: int, guests: int}
     */
    private static function computeTotals(array $rows): array
    {
        $collection = new Collection($rows);

        $menu1 = (int) $collection->sum('menu1');
        $menu2 = (int) $collection->sum('menu2');

        return [
            'clients' => $collection->count(),
            'menu1' => $menu1,
            'menu2' => $menu2,
            'guests' => $menu1 + $menu2,
        ];
    }
}
