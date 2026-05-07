<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\RelationManagers;

use AcMarche\MealDelivery\Filament\Resources\Orders\OrderResource;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use Carbon\CarbonImmutable;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Override;

final class OrdersRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'orders';

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
                ->tooltip('Détails de la commande')
                ->state(fn (Order $record): string => mb_trim(
                    ($record->client?->last_name ?? '').' '.($record->client?->first_name ?? ''),
                ))
                ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record->id]))
                ->searchable(['client.last_name', 'client.first_name'])
                ->sortable(['clients.last_name', 'clients.first_name']),
        ];

        $dayBorder = ['class' => 'border-l border-amber-500 dark:border-gray-700'];

        foreach ($days as $day) {
            $label = Str::title(CarbonImmutable::parse($day)->translatedFormat('D j/m'));

            $columns[] = ColumnGroup::make($label)
                ->alignCenter()
                ->columns([
                    TextColumn::make("day_{$day}_soup")
                        ->label('P')
                        ->alignCenter()
                        // ->extraHeaderAttributes($dayBorder)
                        ->extraCellAttributes($dayBorder)
                        ->state(fn (Order $record): int => self::soupCountForDay($record, $day)),
                    TextColumn::make("day_{$day}_menu1")
                        ->label('M1')
                        ->alignCenter()
                        ->state(fn (Order $record): int => self::menuCountForDay($record, $day, 1)),
                    TextColumn::make("day_{$day}_menu2")
                        ->label('M2')
                        ->alignCenter()
                        ->state(fn (Order $record): int => self::menuCountForDay($record, $day, 2)),
                ]);
        }

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['client', 'meals.menus'])
                ->leftJoin('clients', 'clients.id', '=', 'orders.client_id')
                ->select('orders.*'))
            ->defaultSort('clients.last_name')
            ->paginated(false)
            ->columns($columns)
            ->recordActions([
                EditAction::make(),
            ]);
    }

    private static function mealForDay(Order $order, string $day): ?Meal
    {
        return $order->meals->first(
            fn (Meal $meal): bool => $meal->date?->format('Y-m-d') === $day,
        );
    }

    private static function soupCountForDay(Order $order, string $day): int
    {
        return (int) (self::mealForDay($order, $day)?->soup_count ?? 0);
    }

    private static function menuCountForDay(Order $order, string $day, int $position): int
    {
        $meal = self::mealForDay($order, $day);

        return (int) $meal?->menus
            ->where('position', $position)
            ->sum('quantity');

    }
}
