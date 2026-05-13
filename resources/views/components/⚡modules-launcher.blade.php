<?php

use AcMarche\Security\Handler\MigrationHandler;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component {
    public function getModules(): Collection
    {
        return MigrationHandler::getAllModules();
    }
};
?>

<div>
    {{-- Trigger Button (rendered in sidebar) --}}
        <button
            x-data
            @click="window.dispatchEvent(new CustomEvent('toggle-modules-launcher'))"
            type="button"
            class="cursor-pointer flex w-full items-center justify-center gap-2 rounded-lg bg-pink-400 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-900 focus:outline-none focus:ring-2 focus:ring-cyan-900 focus:ring-offset-2 dark:bg-purple-700 dark:hover:bg-purple-600"
        >
            <x-filament::icon icon="heroicon-m-squares-2x2" class="h-5 w-5"/>
            Applications
        </button>

    @php
        $colors = [
            '#4285F4', '#EA4335', '#FBBC04', '#34A853', '#FF6D01',
            '#46BDC6', '#7B61FF', '#E91E63', '#00ACC1', '#8E24AA',
            '#43A047', '#F4511E',
        ];
    @endphp

    {{-- Modal (teleported to body via portal) --}}
    <template x-teleport="body">
        <div
            x-data="{ open: false, search: '' }"
            x-on:toggle-modules-launcher.window="open = !open; if (open) { $nextTick(() => $refs.searchInput?.focus()) }"
            x-on:keydown.escape.window="open = false"
        >
            {{-- Backdrop --}}
            <div
                x-show="open"
                x-on:click="open = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-40 bg-black/25 dark:bg-black/50"
                style="display: none;"
            ></div>

            {{-- Modal Panel --}}
            <div
                x-show="open"
                x-on:click.outside="open = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="scale-95 opacity-0"
                x-transition:enter-end="scale-100 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="scale-100 opacity-100"
                x-transition:leave-end="scale-95 opacity-0"
                class="fixed top-20 left-4 right-4 z-50 mx-auto max-w-5xl max-h-[calc(100vh-7rem)] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700"
                style="display: none;"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modules-launcher-title"
            >
                {{-- Header --}}
                <div class="mb-5 flex items-center justify-between gap-4">
                    <h2 id="modules-launcher-title" class="text-lg font-bold text-gray-900 dark:text-white">
                        Applications
                    </h2>
                    <div class="relative flex-1 max-w-xs">
                        <x-filament::icon
                            icon="heroicon-m-magnifying-glass"
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                        />
                        <input
                            x-ref="searchInput"
                            x-model="search"
                            type="search"
                            placeholder="Rechercher…"
                            aria-label="Rechercher une application"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-9 pr-3 text-sm text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500 dark:focus:bg-gray-900"
                        />
                    </div>
                    <button
                        x-on:click="open = false"
                        type="button"
                        aria-label="Fermer"
                        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    >
                        <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5"/>
                    </button>
                </div>

                {{-- Modules list (flat, sorted by name ASC) --}}
                <ul role="list" class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($this->getModules() as $moduleIndex => $module)
                        @php
                            $bgColor = $module->color ?: $colors[$moduleIndex % count($colors)];
                            $initials = mb_strtoupper(mb_substr($module->name, 0, 2));
                            $searchKey = mb_strtolower($module->name . ' ' . ($module->description ?? ''));
                        @endphp

                        <li
                            x-show="search === '' || @js($searchKey).includes(search.toLowerCase())"
                            x-transition.opacity
                        >
                            @if ($module->migrated)
                                <a
                                    href="{{ $module->is_external ? $module->url : url($module->url) }}"
                                    @if ($module->is_external) target="_blank" rel="noopener noreferrer" @endif
                                    class="group flex h-full items-start gap-3 rounded-xl border border-transparent p-3 transition hover:border-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:hover:border-gray-700 dark:hover:bg-gray-800/60"
                                >
                                    <span
                                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white shadow-sm transition group-hover:scale-105"
                                        style="background-color: {{ $bgColor }}"
                                        aria-hidden="true"
                                    >
                                        {{ $initials }}
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-center gap-1.5">
                                            <span class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $module->name }}
                                            </span>
                                            @if ($module->is_external)
                                                <x-filament::icon
                                                    icon="heroicon-m-arrow-top-right-on-square"
                                                    class="h-3.5 w-3.5 flex-shrink-0 text-gray-400"
                                                />
                                            @endif
                                        </span>
                                        @if ($module->description)
                                            <span class="mt-0.5 line-clamp-2 block  text-gray-500 dark:text-gray-400">
                                                {{ $module->description }}
                                            </span>
                                        @endif
                                    </span>
                                </a>
                            @else
                                <div
                                    class="flex h-full cursor-not-allowed items-start gap-3 rounded-xl p-3 opacity-50"
                                    aria-disabled="true"
                                >
                                    <span
                                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-gray-300 text-sm font-bold text-white dark:bg-gray-600"
                                        aria-hidden="true"
                                    >
                                        {{ $initials }}
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold text-gray-600 dark:text-gray-400">
                                            {{ $module->name }}
                                        </span>
                                        @if ($module->description)
                                            <span class="mt-0.5 line-clamp-2 block  text-gray-500 dark:text-gray-500">
                                                {{ $module->description }}
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </template>
</div>
