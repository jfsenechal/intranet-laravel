@php
    use AcMarche\KnowledgeBase\Filament\Pages\ListDocuments;
    use AcMarche\KnowledgeBase\Filament\Pages\ShowDocument;
@endphp

<nav class="space-y-6 text-sm">
    @forelse ($navigation as $section)
        <div>
            <h3 class="mb-2 font-semibold text-gray-950 dark:text-white">{{ $section['section'] }}</h3>

            <ul class="space-y-1 border-l border-gray-200 dark:border-white/10">
                @foreach ($section['pages'] as $page)
                    <li>
                        <a
                            href="{{ ShowDocument::getUrl(['document' => $page['slug']]) }}"
                            @class([
                                '-ml-px block border-l py-1 pl-4',
                                'border-primary-600 font-semibold text-primary-600 dark:text-primary-400' => $page['slug'] === $current,
                                'border-transparent text-gray-600 hover:border-gray-400 hover:text-gray-950 dark:text-gray-400 dark:hover:text-white' => $page['slug'] !== $current,
                            ])
                        >
                            {{ $page['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @empty
        <a href="{{ ListDocuments::getUrl() }}" class="text-primary-600 dark:text-primary-400">
            Toutes les pages
        </a>
    @endforelse
</nav>
