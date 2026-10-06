<x-filament-panels::page>
    @php
        $categories = $this->getParentCategories();
        $latestFiches = $this->getLatestFiches();
        $absences = $this->getAbsences();
    @endphp

    <div class="grid grid-cols-1 gap-x-8 gap-y-10 md:grid-cols-2">
        <section class="flex flex-col gap-3">
            <h2 class="text-lg font-semibold">Dernières fiches</h2>

            @if ($latestFiches->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">Aucune fiche.</p>
            @else
                <ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 dark:divide-white/10 dark:border-white/10">
                    @foreach ($latestFiches as $fiche)
                        <li class="flex items-center justify-between gap-4 px-4 py-2.5 text-sm">
                            <a
                                href="{{ $this->getFicheUrl($fiche) }}"
                                class="truncate text-primary-600 hover:underline dark:text-primary-400"
                            >
                                {{ $fiche->name }}
                            </a>
                            <span class="shrink-0 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                {{ $fiche->createdAt?->format('d/m/Y') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="text-lg font-semibold">Absences</h2>

            @if ($absences->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">Aucune absence en cours ou à venir.</p>
            @else
                <ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 dark:divide-white/10 dark:border-white/10">
                    @foreach ($absences as $absence)
                        <li class="flex items-center justify-between gap-4 px-4 py-2.5 text-sm">
                            <a
                                href="{{ $this->getFicheUrl($absence) }}"
                                class="truncate text-primary-600 hover:underline dark:text-primary-400"
                            >
                                {{ $absence->name }}
                            </a>
                            <span class="shrink-0 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                @if ($absence->date_begin && $absence->date_end)
                                    du {{ $absence->date_begin->format('d/m/Y') }} au {{ $absence->date_end->format('d/m/Y') }}
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <div class="mt-10 grid grid-cols-1 gap-x-8 gap-y-10 md:grid-cols-2">
        @foreach ($categories as $category)
            <section class="flex flex-col gap-3">
                <a
                    href="{{ $this->getCategoryUrl($category) }}"
                    class="flex items-center gap-2 text-lg font-semibold hover:underline"
                    @if ($category->color) style="color: {{ $category->color }}" @endif
                >
                    @if ($category->icon)
                        @svg($category->icon, 'h-6 w-6 shrink-0')
                    @endif
                    <span>{{ $category->name }}</span>
                </a>

                @if ($category->children->isNotEmpty())
                    <ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 dark:divide-white/10 dark:border-white/10">
                        @foreach ($category->children as $child)
                            <li class="flex items-center justify-between gap-4 px-4 py-2.5 text-sm">
                                <a
                                    href="{{ $this->getCategoryUrl($child) }}"
                                    class="truncate text-primary-600 hover:underline dark:text-primary-400"
                                >
                                    {{ $child->name }}
                                </a>
                                <span class="shrink-0 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    {{ $child->fiches_count }} fiches
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
