@php($snippetId = 'signature-html-'.\Illuminate\Support\Str::random(8))
<div class="space-y-4">
    <textarea
        id="{{ $snippetId }}"
        rows="14"
        readonly
        class="w-full font-mono text-xs p-3 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-900 dark:text-gray-100"
    >{{ $html }}</textarea>

    <div class="flex gap-2">
        <x-filament::button
            type="button"
            icon="heroicon-o-clipboard-document"
            x-on:click="
                const el = document.getElementById('{{ $snippetId }}');
                el.select();
                navigator.clipboard.writeText(el.value).then(() => {
                    $el.textContent = 'Copié !';
                    setTimeout(() => { $el.textContent = 'Copier dans le presse-papier'; }, 1500);
                });
            "
        >
            Copier dans le presse-papier
        </x-filament::button>
    </div>
</div>
