<x-filament-panels::page>
    <div class="grid gap-6 md:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">S'abonner</x-slot>
            <x-slot name="description">
                Renseignez votre email professionnel ou privé.
            </x-slot>

            <form wire:submit="subscribe" class="space-y-4">
                {{ $this->subscribeForm }}

                <div class="flex justify-end">
                    <x-filament::button type="submit" icon="heroicon-o-envelope">
                        S'abonner
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Se désabonner</x-slot>
            <x-slot name="description">
                Indiquez l'email que vous souhaitez retirer de la liste.
            </x-slot>

            <form wire:submit="unsubscribe" class="space-y-4">
                {{ $this->unsubscribeForm }}

                <div class="flex justify-end">
                    <x-filament::button type="submit" color="danger" icon="heroicon-o-envelope">
                        Se désabonner
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    </div>

    @if ($subscribers->isNotEmpty())
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                Abonnés actuels ({{ $subscribers->count() }})
            </x-slot>

            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($subscribers as $subscriber)
                    <li class="py-2 flex justify-between text-sm">
                        <span>{{ $subscriber->last_name }} {{ $subscriber->first_name }}</span>
                        <span class="text-gray-500">{{ $subscriber->email }}</span>
                    </li>
                @endforeach
            </ul>
        </x-filament::section>
    @endif
</x-filament-panels::page>
