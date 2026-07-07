<x-filament-panels::page>
    @php
        $categories = $this->getParentCategories();
    @endphp

    <div class="grid grid-cols-1 gap-x-8 gap-y-10 md:grid-cols-2">
        @foreach ($categories as $category)
            <section class="flex flex-col gap-3">
                <a
                    href="{{ $this->getCategoryUrl($category) }}"
                    class="flex items-center gap-2 text-lg font-semibold hover:underline"
                    @if ($category->color) style="color: {{ $category->color }}" @endif
                >
                    @if ($category->icon)
                        @svg($category->icon, 'h-6 w-6 shrink-0')
                    @endif
                    <span>{{ $category->name }}</span>
                </a>

                @if ($category->children->isNotEmpty())
                    <ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 dark:divide-white/10 dark:border-white/10">
                        @foreach ($category->children as $child)
                            <li class="flex items-center justify-between gap-4 px-4 py-2.5 text-sm">
                                <a
                                    href="{{ $this->getCategoryUrl($child) }}"
                                    class="truncate text-primary-600 hover:underline dark:text-primary-400"
                                >
                                    {{ $child->name }}
                                </a>
                                <span class="shrink-0 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    {{ $child->fiches_count }} fiches
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
