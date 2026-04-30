<?php

use AcMarche\Document\Filament\Resources\Documents\DocumentResource;
use AcMarche\Document\Models\Document;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    /**
     * @return Collection<int, Document>
     */
    public function documents(): Collection
    {
        return Document::query()->latest('created_at')->limit(6)->get();
    }

    public function with(): array
    {
        return ['latestDocuments' => $this->documents()];
    }
};
?>

<div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up" style="--delay: 0.35s">
    <div class="gradient-documents flex items-center justify-between p-5 text-white">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 backdrop-blur">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h2 class="text-lg font-bold">Derniers documents utilisés</h2>
        </div>
        <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold backdrop-blur">
            {{ $latestDocuments->count() }}
        </span>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse ($latestDocuments as $index => $document)
            <a
                href="{{ DocumentResource::getUrl('view', ['record' => $document->id], panel: 'document-panel') }}"
                class="group flex items-start gap-3 p-4 transition hover:bg-gray-50 animate-fade-in-up"
                style="--delay: {{ 0.4 + ($index * 0.05) }}s"
            >
                <div class="mt-1 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 transition-transform group-hover:scale-110">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium text-gray-900 group-hover:text-emerald-600">
                        {{ $document->name }}
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500">
                        {{ $document->created_at?->translatedFormat('d F Y') }}
                        @if ($document->file_name)
                            — {{ $document->file_name }}
                        @endif
                    </p>
                </div>
            </a>
        @empty
            <p class="p-6 text-center text-sm text-gray-500">Aucun document récent.</p>
        @endforelse
    </div>
</div>
