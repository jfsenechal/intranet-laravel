<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Le Conseil sur le site www.marche.be</x-slot>

        <div class="space-y-6">
            @foreach ($this->getLinks() as $link)
                <div class="space-y-1">
                    <h3 class="text-base font-semibold text-success-600 dark:text-success-400">
                        {{ $link['title'] }}
                    </h3>

                    <x-filament::link
                        :href="$link['url']"
                        target="_blank"
                        rel="noopener noreferrer"
                        icon="heroicon-o-arrow-top-right-on-square"
                        icon-position="after"
                    >
                        {{ $link['label'] }}
                    </x-filament::link>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>
