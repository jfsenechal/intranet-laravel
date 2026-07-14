<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\Pages;

use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\CreateOrder;
use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Week;
use AcMarche\MealDelivery\Policies\Concerns\MealDeliveryAuthorization;
use App\Models\User;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Override;

final class AddOrder extends Page
{
    use MealDeliveryAuthorization;

    public Week $record;

    /**
     * @var list<array{route: string, clients: list<array{name: string, url: string}>}>
     */
    public array $groups;

    #[Override]
    protected static string $resource = WeekResource::class;

    protected string $view = 'meal-delivery::filament.resources.weeks.pages.add-order';

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        return $user instanceof User && self::canAccessStatic($user);
    }

    public function mount(Week $record): void
    {
        $this->record = $record;
        $this->groups = $this->buildGroups();
    }

    public function getTitle(): string
    {
        return 'Ajouter une commande – Semaine du '.$this->record->formattedFirstDay();
    }

    public function getSubheading(): string
    {
        return 'Choisissez un client pour créer sa commande de la semaine.';
    }

    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            WeekResource::getUrl() => 'Semaines',
            WeekResource::getUrl('view', ['record' => $this->record->id]) => 'Semaine du '.$this->record->formattedFirstDay(),
            'Ajouter une commande',
        ];
    }

    /**
     * Active clients without an order for this week, grouped by delivery route.
     *
     * @return list<array{route: string, clients: list<array{name: string, url: string}>}>
     */
    private function buildGroups(): array
    {
        /** @var Collection<int, Client> $clients */
        $clients = Client::query()
            ->where('is_active', true)
            ->whereDoesntHave('orders', fn (Builder $query) => $query->where('week_id', $this->record->id))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $groups = [];

        $routes = DeliveryRoute::query()->orderBy('name')->get();

        foreach ($routes as $route) {
            $routeClients = $clients->where('route_id', $route->id);

            if ($routeClients->isEmpty()) {
                continue;
            }

            $groups[] = [
                'route' => (string) $route->name,
                'clients' => $this->mapClients($routeClients),
            ];
        }

        $withoutRoute = $clients->whereNull('route_id');

        if ($withoutRoute->isNotEmpty()) {
            $groups[] = [
                'route' => 'Sans tournée',
                'clients' => $this->mapClients($withoutRoute),
            ];
        }

        return $groups;
    }

    /**
     * @param  Collection<int, Client>  $clients
     * @return list<array{name: string, url: string}>
     */
    private function mapClients(Collection $clients): array
    {
        return $clients
            ->map(fn (Client $client): array => [
                'name' => mb_trim($client->last_name.' '.$client->first_name),
                'url' => CreateOrder::getUrl([
                    'week_id' => $this->record->id,
                    'client_id' => $client->id,
                ]),
            ])
            ->values()
            ->all();
    }
}
