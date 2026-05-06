<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Orders\Pages;

use AcMarche\MealDelivery\Filament\Resources\Orders\OrderResource;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\Week;
use Filament\Resources\Pages\CreateRecord;
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
                    'notes' => null,
                    'menus' => [
                        ['position' => 1, 'quantity' => 0],
                        ['position' => 2, 'quantity' => 0],
                    ],
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
}
