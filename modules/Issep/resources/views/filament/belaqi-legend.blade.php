{{-- The BelAQI scale, shown from the "Légende" action of the stations page. --}}
<div class="fi-modal-content">
    <ul class="divide-y divide-gray-100 dark:divide-white/10">
        @foreach ($indices as $indice)
            <li class="flex items-center justify-between gap-3 py-2">
                <span class="flex items-center gap-3">
                    <span
                        class="size-4 shrink-0 rounded-full ring-1 ring-gray-950/10 dark:ring-white/20"
                        style="background-color: {{ $indice->hex() }}"
                    ></span>

                    <span class="text-sm text-gray-950 dark:text-white">
                        {{ $indice->label() }}
                    </span>
                </span>

                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $indice->value }}
                </span>
            </li>
        @endforeach
    </ul>
</div>
