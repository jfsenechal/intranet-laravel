<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Orders\Pages;

use AcMarche\MealDelivery\Filament\Resources\Orders\OrderResource;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Menu;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Override;

final class CreateOrder extends CreateRecord
{
    public ?int $weekId = null;

    public ?int $clientId = null;

    #[Override]
    protected static string $resource = OrderResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $this->weekId = (int) request()->query('week_id') ?: null;
        $this->clientId = (int) request()->query('client_id') ?: null;

        parent::mount();

        if ($this->weekId === null || $this->clientId === null) {
            return;
        }

        $week = Week::find($this->weekId);

        if (! $week instanceof Week) {
            return;
        }

        $this->form->fill([
            'week_id' => $this->weekId,
            'client_id' => $this->clientId,
            'is_last_meal' => false,
            'meals' => collect($week->days ?? [])
                ->map(fn (string $day): array => [
                    'date' => $day,
                    'soup_count' => 0,
                    'menu_1' => 0,
                    'menu_2' => 0,
                    'notes' => null,
                ])
                ->values()
                ->all(),
        ]);
    }

    public function getTitle(): string
    {
        if ($this->weekId === null || $this->clientId === null) {
            return 'Nouvelle commande';
        }

        $client = Client::find($this->clientId);
        $week = Week::find($this->weekId);

        if (! $client instanceof Client || ! $week instanceof Week) {
            return 'Nouvelle commande';
        }

        return sprintf(
            'Nouveaux repas pour %s %s, semaine du %s',
            $client->last_name,
            $client->first_name,
            $week->first_day->translatedFormat('j F Y'),
        );
    }

    protected function handleRecordCreation(array $data): Model
    {
        $meals = $data['meals'] ?? [];
        unset($data['meals']);

        return DB::connection('maria-meal-delivery')->transaction(function () use ($data, $meals): Order {
            $order = Order::query()->create($data);

            foreach ($meals as $mealData) {
                $menu1 = (int) ($mealData['menu_1'] ?? 0);
                $menu2 = (int) ($mealData['menu_2'] ?? 0);

                $meal = Meal::query()->create([
                    'order_id' => $order->id,
                    'date' => $mealData['date'] ?? null,
                    'soup_count' => (int) ($mealData['soup_count'] ?? 0),
                    'notes' => $mealData['notes'] ?? null,
                ]);

                Menu::query()->create([
                    'meal_id' => $meal->id,
                    'position' => 1,
                    'quantity' => $menu1,
                ]);

                Menu::query()->create([
                    'meal_id' => $meal->id,
                    'position' => 2,
                    'quantity' => $menu2,
                ]);
            }

            return $order;
        });
    }
}
