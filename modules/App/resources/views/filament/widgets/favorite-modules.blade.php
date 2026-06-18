<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Mes favoris</x-slot>

        @php($favorites = $this->getFavorites())

        @forelse ($favorites as $module)
            <a
                href="{{ $module->is_external ? $module->url : url($module->url) }}"
                @if ($module->is_external) target="_blank" rel="noopener noreferrer" @endif
                class="group flex items-center gap-3 border-b border-gray-100 py-2 last:border-0 dark:border-gray-700"
            >
                <span
                    class="flex size-9 flex-shrink-0 items-center justify-center rounded-lg text-sm font-bold text-white shadow-sm transition group-hover:scale-105"
                    style="background-color: {{ $module->color ?: '#f59e0b' }}"
                    aria-hidden="true"
                >
                    {{ mb_strtoupper(mb_substr($module->name, 0, 2)) }}
                </span>
                <span class="min-w-0 flex-1 truncate text-sm font-semibold text-gray-900 group-hover:text-primary-600 dark:text-gray-100 dark:group-hover:text-primary-400">
                    {{ $module->name }}
                </span>
                @if ($module->is_external)
                    <x-filament::icon
                        icon="heroicon-m-arrow-top-right-on-square"
                        class="h-4 w-4 flex-shrink-0 text-gray-400"
                    />
                @endif
            </a>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">Aucun favori pour le moment.</p>
        @endforelse
    </x-filament::section>
</x-filament-widgets::widget>
