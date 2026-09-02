<x-filament-panels::page>
    <div class="mx-auto w-full max-w-4xl">
        <video
            class="w-full rounded-xl shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10"
            src="{{ $this->getVideoUrl() }}"
            controls
            preload="metadata"
            playsinline
        >
            <a href="{{ $this->getVideoUrl() }}">Télécharger la vidéo de démonstration</a>
        </video>
    </div>
</x-filament-panels::page>
