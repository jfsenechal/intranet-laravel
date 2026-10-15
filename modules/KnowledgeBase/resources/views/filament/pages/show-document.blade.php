<x-filament-panels::page>
    <div class="grid gap-8 lg:grid-cols-[16rem_minmax(0,1fr)]">
        <aside class="lg:sticky lg:top-20 lg:self-start">
            @include('knowledge-base::filament.pages.partials.sidebar', ['navigation' => $navigation, 'current' => $this->document])
        </aside>

        <div class="min-w-0 space-y-6">
            @if (filled($vaultUrl))
                <div class="rounded-lg border border-warning-300 bg-warning-50 px-4 py-3 text-sm text-warning-800 dark:border-warning-700 dark:bg-warning-950 dark:text-warning-200">
                    Les mots de passe ne sont jamais stockés ici :
                    <a href="{{ $vaultUrl }}" target="_blank" rel="noopener" class="font-semibold underline">voir le coffre-fort</a>.
                </div>
            @endif

            <x-filament::section>
                <article class="fi-prose max-w-none">
                    {{-- Raw HTML is escaped by CommonMark (html_input = escape) before reaching this point. --}}
                    {!! $html !!}
                </article>
            </x-filament::section>

            <p class="text-xs text-gray-500 dark:text-gray-400">
                Modifié {{ $updatedAt->diffForHumans() }}
            </p>
        </div>
    </div>
</x-filament-panels::page>
