@php
    use AcMarche\MealDelivery\Filament\Resources\Orders\OrderResource;
@endphp

<div class="space-y-2">
    @forelse ($clients as $client)
        <a
            href="{{ OrderResource::getUrl('create', ['week_id' => $week->id, 'client_id' => $client->id]) }}"
            class="flex items-center justify-between rounded-lg px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-white/5"
        >
            <span class="font-medium text-gray-950 dark:text-white">
                {{ mb_trim($client->last_name.' '.$client->first_name) }}
            </span>
            @if ($client->deliveryRoute)
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $client->deliveryRoute->name }}
                </span>
            @endif
        </a>
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Tous les clients actifs ont une commande cette semaine.
        </p>
    @endforelse
</div>
