<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\RelationManagers;

use AcMarche\MealDelivery\Filament\Resources\Orders\OrderResource;
use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\CreateOrder;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use Carbon\CarbonImmutable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Override;

final class OrdersRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'orders';

    /**
     * Custom wrapper adding the `meal-week-grid` class so the panel theme can
     * pin the two header rows (day groups + P/M1/M2) while scrolling.
     */
    #[Override]
    protected string $view = 'meal-delivery::filament.relation-managers.orders';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Commandes ('.$ownerRecord->orders()->count().')';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        /** @var Week $week */
        $week = $this->getOwnerRecord();

        $days = collect($week->days ?? [])
            ->map(fn (string $day): string => CarbonImmutable::parse($day)->format('Y-m-d'))
            ->values()
            ->all();

        $columns = [
            TextColumn::make('client_name')
                ->label('Client')
                ->state(fn (Client $record): string => mb_trim(
                    $record->last_name.' '.$record->first_name,
                ))
                ->description(fn (Client $record): string => self::orderForClient($record) instanceof Order
                    ? 'Détails de la commande'
                    : 'Ajouter une commande')
                ->url(fn (Client $record): string => self::orderForClient($record) instanceof Order
                    ? OrderResource::getUrl('view', ['record' => self::orderForClient($record)->id])
                    : CreateOrder::getUrl(['week_id' => $week->id, 'client_id' => $record->id])),
        ];

        $dayBorder = ['class' => 'border-l border-amber-500 dark:border-gray-700'];

        foreach ($days as $day) {
            $label = Str::title(CarbonImmutable::parse($day)->translatedFormat('D j/m'));

            $columns[] = ColumnGroup::make($label)
                ->alignCenter()
                ->columns([
                    TextColumn::make("day_{$day}_soup")
                        ->label('P')
                        ->tooltip('Potage')
                        ->alignCenter()
                        ->extraCellAttributes($dayBorder)
                        ->state(fn (Client $record): int|string => self::soupCountForDay($record, $day)),
                    TextColumn::make("day_{$day}_menu1")
                        ->label('M1')
                        ->tooltip('Menu 1')
                        ->alignCenter()
                        ->state(fn (Client $record): int|string => self::menuCountForDay($record, $day, 1)),
                    TextColumn::make("day_{$day}_menu2")
                        ->label('M2')
                        ->tooltip('Menu 2')
                        ->alignCenter()
                        ->state(fn (Client $record): int|string => self::menuCountForDay($record, $day, 2)),
                ]);
        }

        return $table
            ->records(fn (): Collection => self::activeClientsForWeek($week))
            ->recordClasses(fn (Client $record): ?string => self::orderForClient($record) instanceof Order
                ? null
                : 'bg-amber-50 dark:bg-amber-400/10')
            ->paginated(false)
            ->columns($columns);
    }

    /**
     * Every active client, with the order (and its meals) for the given week eager loaded.
     *
     * @return Collection<int, Client>
     */
    private static function activeClientsForWeek(Week $week): Collection
    {
        return Client::query()
            ->where('is_active', true)
            ->with(['orders' => fn ($orders) => $orders
                ->where('week_id', $week->id)
                ->with('meals.menus')])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    private static function orderForClient(Client $client): ?Order
    {
        return $client->orders->first();
    }

    private static function mealForDay(Client $client, string $day): ?Meal
    {
        return self::orderForClient($client)?->meals->first(
            fn (Meal $meal): bool => $meal->date?->format('Y-m-d') === $day,
        );
    }

    private static function soupCountForDay(Client $client, string $day): int|string
    {
        $meal = self::mealForDay($client, $day);

        return $meal instanceof Meal ? (int) $meal->soup_count : '';
    }

    private static function menuCountForDay(Client $client, string $day, int $position): int|string
    {
        $meal = self::mealForDay($client, $day);

        if (! $meal instanceof Meal) {
            return '';
        }

        return (int) $meal->menus
            ->where('position', $position)
            ->sum('quantity');
    }
}
