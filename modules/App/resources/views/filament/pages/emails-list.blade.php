<x-filament-panels::page>
    <x-filament::section>
        <table class="w-full">
            <thead>
            <tr class="text-left">
                <th class="py-2 pr-4">Mail</th>
                <th class="py-2">Destinataires</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($data as $item)
                <tr class="border-t border-gray-200 dark:border-gray-700 align-top">
                    <td class="py-2 pr-4">
                        <a href="mailto:{{ $item['mail'] }}" class="text-primary-600 hover:underline">{{ $item['mail'] }}</a>
                        @if ($item['description'])
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item['description'] }}</div>
                        @endif
                    </td>
                    <td class="py-2">
                        @if ($item['proxyaddresses'] !== [])
                            <x-filament::dropdown placement="bottom-start">
                                <x-slot name="trigger">
                                    <button type="button" class="text-primary-600 hover:underline">
                                        Voir ({{ count($item['proxyaddresses']) }})
                                    </button>
                                </x-slot>

                                <div class="p-3 space-y-1">
                                    @foreach ($item['proxyaddresses'] as $address)
                                        <div>{{ $address }}</div>
                                    @endforeach
                                </div>
                            </x-filament::dropdown>
                            @else
                            <div>Boîte indépendante</div>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-filament::section>
</x-filament-panels::page>
