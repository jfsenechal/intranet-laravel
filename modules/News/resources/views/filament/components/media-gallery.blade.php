<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $medias = collect($getState() ?? [])->filter()->values();
        $isImage = fn (string $path): bool => in_array(
            strtolower(pathinfo($path, PATHINFO_EXTENSION)),
            ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg'],
            true,
        );

        $images = $medias
            ->filter($isImage)
            ->map(fn (string $path) => \Illuminate\Support\Facades\Storage::disk('public')->url($path))
            ->values()
            ->all();

        $files = $medias
            ->reject($isImage)
            ->map(fn (string $path) => [
                'name' => basename($path),
                'url' => \Illuminate\Support\Facades\Storage::disk('public')->url($path),
            ])
            ->values()
            ->all();
    @endphp

    @if (! empty($images) || ! empty($files))
        <div
            x-data="{
                open: false,
                index: 0,
                images: @js($images),
                show(i) {
                    this.index = i;
                    this.open = true;
                },
                close() { this.open = false; },
                next() { this.index = (this.index + 1) % this.images.length; },
                prev() { this.index = (this.index - 1 + this.images.length) % this.images.length; },
            }"
            x-on:keydown.escape.window="close()"
            x-on:keydown.arrow-right.window="open && next()"
            x-on:keydown.arrow-left.window="open && prev()"
            class="space-y-3"
        >
            @if (! empty($images))
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    @foreach ($images as $i => $url)
                        <button
                            type="button"
                            x-on:click="show({{ $i }})"
                            class="group relative aspect-square overflow-hidden rounded-lg ring-1 ring-gray-200 transition hover:ring-primary-500 dark:ring-white/10"
                        >
                            <img
                                src="{{ $url }}"
                                alt=""
                                loading="lazy"
                                class="size-full object-cover transition duration-200 group-hover:scale-105"
                            />
                        </button>
                    @endforeach
                </div>
            @endif

            @if (! empty($files))
                <ul class="flex flex-wrap gap-2">
                    @foreach ($files as $file)
                        <li>
                            <a
                                href="{{ $file['url'] }}"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-200 transition hover:bg-gray-100 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/10"
                            >
                                <x-filament::icon icon="tabler-file-download" class="size-5 text-primary-500" />
                                {{ $file['name'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            <template x-teleport="body">
                <div
                    x-show="open"
                    x-cloak
                    x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-sm"
                    x-on:click.self="close()"
                >
                    <button
                        type="button"
                        x-on:click="close()"
                        class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white transition hover:bg-white/20"
                        aria-label="Fermer"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <template x-if="images.length > 1">
                        <button
                            type="button"
                            x-on:click.stop="prev()"
                            class="absolute left-4 rounded-full bg-white/10 p-2 text-white transition hover:bg-white/20"
                            aria-label="Précédent"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                    </template>

                    <img
                        :src="images[index]"
                        x-on:click.stop
                        alt=""
                        class="max-h-[90vh] max-w-[90vw] rounded-lg object-contain shadow-2xl"
                    />

                    <template x-if="images.length > 1">
                        <button
                            type="button"
                            x-on:click.stop="next()"
                            class="absolute right-4 rounded-full bg-white/10 p-2 text-white transition hover:bg-white/20"
                            aria-label="Suivant"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </template>

                    <template x-if="images.length > 1">
                        <div
                            class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-white/10 px-3 py-1 text-sm text-white"
                            x-text="`${index + 1} / ${images.length}`"
                        ></div>
                    </template>
                </div>
            </template>
        </div>
    @endif
</x-dynamic-component>
