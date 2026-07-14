<x-filament-panels::page>
    @if (empty($groups))
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Tous les clients actifs ont déjà une commande pour cette semaine.
            </p>
        </x-filament::section>
    @else
        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($groups as $group)
                <x-filament::section>
                    <x-slot name="heading">
                        <span class="text-primary-600 dark:text-primary-400">{{ $group['route'] }}</span>
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                            ({{ count($group['clients']) }})
                        </span>
                    </x-slot>

                    <ul role="list" class="-my-2 divide-y divide-gray-100 dark:divide-white/10">
                        @foreach ($group['clients'] as $client)
                            <li>
                                <a
                                    href="{{ $client['url'] }}"
                                    wire:navigate
                                    class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 hover:text-primary-600 dark:text-gray-200 dark:hover:bg-white/5 dark:hover:text-primary-400"
                                >
                                    {{ $client['name'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </x-filament::section>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
