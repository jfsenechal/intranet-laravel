<x-filament-panels::page>
    <div class="space-y-8">
        @foreach ($this->getTabsWithModules() as $tab)
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                {{-- Heading Section --}}
                {{-- :icon="$tab->icon" --}}

                <div class="mb-6 flex items-center gap-3">
                    @if ($tab->icon)
                        <x-filament::icon
                            icon="heroicon-o-arrow-top-right-on-square"
                            class="h-8 w-8 text-primary-600 dark:text-primary-400"
                        />
                    @endif
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $tab->name }}
                    </h2>
                </div>

                {{-- Modules Grid --}}
                @if ($tab->modules->isNotEmpty())
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($tab->modules as $module)
                            <a
                                href="{{ $module->url }}"
                                @if ($module->is_external) target="_blank" rel="noopener noreferrer" @endif
                                class="group flex items-start gap-4 rounded-lg border border-gray-200 p-4 transition hover:border-primary-500 hover:bg-gray-50 dark:border-gray-700 dark:hover:border-primary-500 dark:hover:bg-gray-700/50"
                            >
                                {{-- Module Icon --}}
                                {{--      :icon="$module->icon" --}}
                                @if ($module->icon)
                                    <div class="flex-shrink-0">
                                        <x-filament::icon

                                            icon="heroicon-o-arrow-top-right-on-square"
                                            class="h-6 w-6 transition group-hover:text-primary-600 dark:group-hover:text-primary-400"
                                            :style="$module->color ? 'color: ' . $module->color : ''"
                                        />
                                    </div>
                                @endif

                                {{-- Module Content --}}
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">
                                        {{ $module->name }}
                                        @if ($module->is_external)
                                            <x-filament::icon
                                                icon="heroicon-o-arrow-top-right-on-square"
                                                class="inline h-3 w-3"
                                            />
                                        @endif
                                    </h3>
                                    @if ($module->description)
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $module->description }}
                                        </p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Aucun module disponible dans cette catégorie.
                    </p>
                @endif
            </div>
        @endforeach

        @if ($this->getTabsWithModules()->isEmpty())
            <div
                class="rounded-lg border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
                <p class="text-gray-500 dark:text-gray-400">
                    Aucune catégorie de modules disponible.
                </p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
