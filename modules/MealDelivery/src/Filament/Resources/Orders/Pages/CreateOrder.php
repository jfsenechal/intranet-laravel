<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Orders\Pages;

use AcMarche\MealDelivery\Filament\Resources\Orders\OrderResource;
use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Menu;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Override;

final class CreateOrder extends CreateRecord
{
    public ?int $weekId = null;

    public ?int $clientId = null;

    /**
     * Set when the "create and add another" button is used, so the redirect goes
     * back to the week's client picker instead of the created order.
     */
    protected bool $createAnotherRequested = false;

    /**
     * The order, meals and menus are persisted in an explicit transaction on the
     * meal-delivery connection inside handleRecordCreation(), so Filament's implicit
     * transaction on the default connection is redundant.
     */
    protected ?bool $hasDatabaseTransactions = false;

    #[Override]
    protected static string $resource = OrderResource::class;

    public function mount(): void
    {
        $this->weekId = (int) request()->query('week_id') ?: null;
        $this->clientId = (int) request()->query('client_id') ?: null;

        parent::mount();

        if ($this->weekId === null || $this->clientId === null) {
            return;
        }

        $existingOrder = Order::query()
            ->where('week_id', $this->weekId)
            ->where('client_id', $this->clientId)
            ->first();

        if ($existingOrder instanceof Order) {
            Notification::make()
                ->warning()
                ->title('Une commande existe déjà pour ce client et cette semaine.')
                ->send();

            $this->redirect(OrderResource::getUrl('edit', ['record' => $existingOrder]));

            return;
        }

        $week = Week::find($this->weekId);

        if (! $week instanceof Week) {
            return;
        }

        $atCafeteria = (bool) (Client::find($this->clientId)?->use_cafeteria ?? false);

        $this->form->fill([
            'week_id' => $this->weekId,
            'client_id' => $this->clientId,
            'is_last_meal' => false,
            'meals' => collect($week->days ?? [])
                ->map(fn (string $day): array => [
                    'date' => $day,
                    'soup_count' => 0,
                    'menu_1' => 0,
                    'menu_1_diets' => [],
                    'menu_2' => 0,
                    'menu_2_diets' => [],
                    'at_cafeteria' => $atCafeteria,
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
            $week->formattedFirstDay(),
        );
    }

    /**
     * Records which button was pressed, then always performs a redirect
     * (never the default "create another" form reset).
     */
    #[Override]
    public function create(bool $another = false): void
    {
        $this->createAnotherRequested = $another;

        parent::create(another: false);
    }

    #[Override]
    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Créer et ajouter un autre');
    }

    #[Override]
    protected function getRedirectUrl(): string
    {
        if ($this->createAnotherRequested && $this->weekId !== null) {
            return WeekResource::getUrl('add-order', ['record' => $this->weekId]);
        }

        return parent::getRedirectUrl();
    }

    protected function handleRecordCreation(array $data): Model
    {
        $meals = $data['meals'] ?? [];
        unset($data['meals']);

        $existingOrder = Order::query()
            ->where('week_id', $data['week_id'])
            ->where('client_id', $data['client_id'])
            ->first();

        if ($existingOrder instanceof Order) {
            Notification::make()
                ->warning()
                ->title('Une commande existe déjà pour ce client et cette semaine.')
                ->send();

            $this->redirect(OrderResource::getUrl('edit', ['record' => $existingOrder]));

            throw new Halt;
        }

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
                    'at_cafeteria' => (bool) ($mealData['at_cafeteria'] ?? false),
                ]);

                Menu::query()->create([
                    'meal_id' => $meal->id,
                    'position' => 1,
                    'quantity' => $menu1,
                ])->diets()->sync($mealData['menu_1_diets'] ?? []);

                Menu::query()->create([
                    'meal_id' => $meal->id,
                    'position' => 2,
                    'quantity' => $menu2,
                ])->diets()->sync($mealData['menu_2_diets'] ?? []);
            }

            return $order;
        });
    }
}
