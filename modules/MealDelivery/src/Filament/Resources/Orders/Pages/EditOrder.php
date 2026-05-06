<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Orders\Pages;

use AcMarche\MealDelivery\Filament\Resources\Orders\OrderResource;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Menu;
use AcMarche\MealDelivery\Models\Order;
use Carbon\CarbonImmutable;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Override;

final class EditOrder extends EditRecord
{
    #[Override]
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        /** @var Order $order */
        $order = $this->record;
        $client = $order->client;
        $week = $order->week;

        if ($client === null || $week === null) {
            return 'Modifier la commande';
        }

        return sprintf(
            'Repas pour %s %s, semaine du %s',
            $client->last_name,
            $client->first_name,
            $week->first_day->translatedFormat('j F Y'),
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Order $order */
        $order = $this->record;

        $existingMeals = $order->meals()
            ->with('menus')
            ->orderBy('date')
            ->get()
            ->keyBy(fn (Meal $meal): string => $meal->date->format('Y-m-d'));

        $days = collect($order->week?->days ?? [])
            ->map(fn (string $day): string => CarbonImmutable::parse($day)->format('Y-m-d'))
            ->values();

        if ($days->isEmpty()) {
            $days = $existingMeals->keys();
        }

        $data['meals'] = $days
            ->map(function (string $day) use ($existingMeals): array {
                $meal = $existingMeals->get($day);
                $menus = $meal?->menus ?? collect();

                return [
                    'date' => $day,
                    'soup_count' => (int) ($meal->soup_count ?? 0),
                    'menu_1' => (int) ($menus->firstWhere('position', 1)->quantity ?? 0),
                    'menu_2' => (int) ($menus->firstWhere('position', 2)->quantity ?? 0),
                    'notes' => $meal->notes ?? null,
                ];
            })
            ->values()
            ->all();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Order $record */
        $meals = $data['meals'] ?? [];
        unset($data['meals']);

        return DB::connection('maria-meal-delivery')->transaction(function () use ($record, $data, $meals): Order {
            $record->update($data);

            $existingMeals = $record->meals()
                ->with('menus')
                ->get()
                ->keyBy(fn (Meal $meal): string => $meal->date->format('Y-m-d'));

            $keptMealIds = [];

            foreach ($meals as $mealData) {
                $date = $mealData['date'] ?? null;

                if ($date === null) {
                    continue;
                }

                $date = CarbonImmutable::parse($date)->format('Y-m-d');
                $meal = $existingMeals->get($date);

                $attributes = [
                    'order_id' => $record->id,
                    'date' => $date,
                    'soup_count' => (int) ($mealData['soup_count'] ?? 0),
                    'notes' => $mealData['notes'] ?? null,
                ];

                if ($meal instanceof Meal) {
                    $meal->update($attributes);
                } else {
                    $meal = Meal::query()->create($attributes);
                }

                $keptMealIds[] = $meal->id;

                self::upsertMenu($meal, 1, (int) ($mealData['menu_1'] ?? 0));
                self::upsertMenu($meal, 2, (int) ($mealData['menu_2'] ?? 0));
            }

            $record->meals()
                ->whereNotIn('id', $keptMealIds)
                ->each(fn (Meal $meal) => $meal->delete());

            return $record;
        });
    }

    private static function upsertMenu(Meal $meal, int $position, int $quantity): void
    {
        $menu = $meal->menus->firstWhere('position', $position);

        if ($menu instanceof Menu) {
            $menu->update(['quantity' => $quantity]);

            return;
        }

        Menu::query()->create([
            'meal_id' => $meal->id,
            'position' => $position,
            'quantity' => $quantity,
        ]);
    }
}
