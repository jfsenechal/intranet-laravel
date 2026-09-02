<x-filament-panels::page>
    <ul role="list" class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @foreach ($this->getQrCodeActions() as $action)
            <li>
                <a
                    href="{{ $this->getActionUrl($action) }}"
                    class="flex h-full items-start gap-4 rounded-xl border border-gray-200 bg-white p-4 transition hover:border-primary-500 hover:shadow-sm dark:border-white/10 dark:bg-white/5 dark:hover:border-primary-400"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        @svg($action->getIcon(), 'h-6 w-6')
                    </span>

                    <span class="flex flex-col gap-1">
                        <span class="font-semibold text-gray-950 dark:text-white">
                            {{ $action->getLabel() }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $action->getDescription() }}
                        </span>
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
</x-filament-panels::page>
