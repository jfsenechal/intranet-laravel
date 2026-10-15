{{-- Menu of the page being read, built from its own link list or headings. --}}
<nav class="text-sm" aria-label="Sur cette page">
    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        Sur cette page
    </h3>

    <ul class="space-y-1 border-l border-gray-200 dark:border-white/10">
        @foreach ($menu as $item)
            <li>
                <a
                    href="#{{ $item['anchor'] }}"
                    class="-ml-px block border-l border-transparent py-1 pl-4 font-medium text-gray-700 hover:border-primary-600 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400"
                >
                    {{ $item['label'] }}
                </a>

                @if ($item['children'] !== [])
                    <ul class="space-y-1">
                        @foreach ($item['children'] as $child)
                            <li>
                                <a
                                    href="#{{ $child['anchor'] }}"
                                    class="-ml-px block border-l border-transparent py-1 pl-8 text-gray-500 hover:border-primary-600 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400"
                                >
                                    {{ $child['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>
</nav>
