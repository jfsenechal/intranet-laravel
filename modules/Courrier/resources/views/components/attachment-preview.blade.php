<div class="w-full rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
    @if (str_starts_with($contentType, 'image/'))
        <img
            src="{{ $url }}"
            alt="{{ $filename }}"
            class="mx-auto max-h-[600px] rounded-lg object-contain lg:max-h-[calc(100vh-10rem)]"
        />
    @elseif ($contentType === 'application/pdf')
        <iframe
            src="{{ $url }}"
            class="h-[600px] w-full rounded-lg border-0 lg:h-[calc(100vh-10rem)]"
            title="{{ $filename }}"
        ></iframe>
    @else
        <div class="flex flex-col items-center justify-center py-8">
            <x-filament::icon
                icon="tabler-file"
                class="mb-2 h-12 w-12 text-gray-400"
            />
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $filename }}
            </p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                {{ $contentType }}
            </p>
        </div>
    @endif
</div>
