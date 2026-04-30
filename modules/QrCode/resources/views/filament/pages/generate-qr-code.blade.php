<x-filament-panels::page>
    <form wire:submit="generate">
        {{ $this->form }}

        <div class="mt-6 flex flex-wrap gap-3">
            @foreach ($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>

    @if ($previewMarkup)
        <x-filament::section class="mt-8">
            <x-slot name="heading">Aperçu</x-slot>

            <div class="flex flex-col items-center gap-4">
                <div class="max-w-md w-full bg-white p-4 rounded-lg shadow">
                    {!! $previewMarkup !!}
                </div>

                <div>
                    {{ $this->downloadAction }}
                </div>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
