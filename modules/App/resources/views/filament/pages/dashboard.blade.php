@php use AcMarche\App\Filament\Pages\DashboardPage; @endphp
<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        <x-filament::section class="lg:col-span-2">
            <x-slot name="heading">Mes applications favorites</x-slot>

            @forelse ($this->favoriteModules as $module)
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

        <x-filament::section>
            <x-slot name="heading">Mes courriers (Indicateur)</x-slot>

            @forelse ($this->ownedCourriers as $courrier)
                <div
                    class="flex items-start justify-between gap-4 border-b border-gray-200 py-2 last:border-0 dark:border-gray-700">
                    <div class="min-w-0">
                        <p class="truncate font-medium text-gray-900 dark:text-gray-100">
                            {{ $courrier->reference_number }} — {{ $courrier->sender }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $courrier->mail_date?->translatedFormat('d/m/Y') }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">Vous n'avez encore enregistré aucun courrier.</p>
            @endforelse
        </x-filament::section>


        <x-filament::section>
            <x-slot name="heading">Dernières actualités</x-slot>

            @forelse ($this->latestNews as $news)
                <div
                    class="flex items-start justify-between gap-4 border-b border-gray-200 py-2 last:border-0 dark:border-gray-700">
                    <div class="min-w-0">
                        <p class="truncate font-medium text-gray-900 dark:text-gray-100">
                            {{ $news->name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $news->created_at?->translatedFormat('d/m/Y') }}
                            @if ($news->user_add)
                                — {{ $news->user_add }}
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">Aucune actualité récente.</p>
            @endforelse
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Derniers documents</x-slot>

            @forelse ($this->latestDocuments as $document)
                <div
                    class="flex items-start justify-between gap-4 border-b border-gray-200 py-2 last:border-0 dark:border-gray-700">
                    <div class="min-w-0">
                        <p class="truncate font-medium text-gray-900 dark:text-gray-100">
                            {{ $document->name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $document->category?->name }}
                            - {{ $document->created_at?->translatedFormat('d/m/Y') }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">Aucun document récent.</p>
            @endforelse
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Flux RSS</x-slot>

            <ul class="space-y-2">
                @foreach (DashboardPage::RSS_FEEDS as $feed)
                    <li class="flex items-center gap-2">
                        <x-filament::icon
                            icon="heroicon-o-rss"
                            class="h-4 w-4 text-primary-500"
                        />
                        <a
                            href="{{ $feed['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-sm text-primary-600 hover:underline dark:text-primary-400"
                        >
                            {{ $feed['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-filament::section>

    </div>
</x-filament-panels::page>
