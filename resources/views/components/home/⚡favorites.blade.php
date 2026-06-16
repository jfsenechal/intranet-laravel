<?php

use AcMarche\App\Handler\FavoriteModuleHandler;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    /**
     * @return Collection<int, \AcMarche\Security\Models\Module>
     */
    public function favorites(): Collection
    {
        return FavoriteModuleHandler::getFavoriteModules();
    }

    #[On('favorites-updated')]
    public function refreshFavorites(): void
    {
        // The list is recomputed on re-render; this listener just triggers it.
    }
};
?>

<div class="card-hover relative overflow-hidden rounded-2xl bg-white shadow-lg animate-fade-in-up" style="--delay: 0.15s">
    <div class="flex flex-col gap-4 p-5">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-xl bg-linear-to-br from-amber-400 to-orange-500 text-white shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.48 3.5a.56.56 0 011.04 0l2.07 4.2a.56.56 0 00.42.3l4.64.68a.56.56 0 01.31.96l-3.36 3.27a.56.56 0 00-.16.5l.79 4.62a.56.56 0 01-.81.59l-4.15-2.18a.56.56 0 00-.52 0L7.6 18.62a.56.56 0 01-.81-.59l.79-4.62a.56.56 0 00-.16-.5L4.06 9.64a.56.56 0 01.31-.96l4.64-.68a.56.56 0 00.42-.3l2.05-4.2z" />
                </svg>
            </div>
            <h2 class="text-lg font-extrabold text-gray-900">Mes applications favorites</h2>
        </div>

        @php($favorites = $this->favorites())

        @forelse ($favorites as $module)
            <a
                href="{{ $module->is_external ? $module->url : url($module->url) }}"
                @if ($module->is_external) target="_blank" rel="noopener noreferrer" @endif
                class="group flex items-center gap-3 rounded-xl border border-gray-100 p-3 transition hover:border-amber-200 hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-400"
            >
                <span
                    class="flex size-9 flex-shrink-0 items-center justify-center rounded-lg text-sm font-bold text-white shadow-sm transition group-hover:scale-105"
                    style="background-color: {{ $module->color ?: '#f59e0b' }}"
                    aria-hidden="true"
                >
                    {{ mb_strtoupper(mb_substr($module->name, 0, 2)) }}
                </span>
                <span class="min-w-0 flex-1 truncate text-sm font-semibold text-gray-900">
                    {{ $module->name }}
                </span>
                @if ($module->is_external)
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5h5m0 0v5m0-5L10 14M9 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-3" />
                    </svg>
                @endif
            </a>
        @empty
            <p class="text-sm text-gray-500">Aucun favori pour le moment.</p>
        @endforelse
    </div>
</div>
