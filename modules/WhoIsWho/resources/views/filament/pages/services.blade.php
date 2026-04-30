<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1">
            <x-filament::section>
                <x-slot name="heading">
                    Services ({{ $services->count() }})
                </x-slot>
                <ul class="space-y-1 max-h-[70vh] overflow-y-auto">
                    @foreach ($services as $service)
                        <li>
                            <button type="button"
                                    wire:click="selectService({{ $service->id }})"
                                    class="w-full text-left px-3 py-2 text-sm rounded-md
                                        @if ($selectedService?->id === $service->id)
                                            bg-primary-600 text-white
                                        @else
                                            text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800
                                        @endif">
                                {{ $service->name }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </x-filament::section>
        </div>

        <div class="lg:col-span-3">
            @if ($selectedService === null)
                <x-filament::section>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Sélectionnez un service dans la liste pour afficher ses agents.
                    </p>
                </x-filament::section>
            @else
                <x-filament::section>
                    <x-slot name="heading">
                        {{ $selectedService->name }}
                    </x-slot>
                    <x-slot name="description">
                        @if ($employees && $employees->isNotEmpty())
                            {{ $employees->count() }} agent(s) actif(s)
                        @else
                            Aucun agent actif
                        @endif
                    </x-slot>

                    <dl class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        @if ($selectedService->direction)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Direction</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $selectedService->direction->name }}</dd>
                            </div>
                        @endif
                        @if ($selectedService->employer)
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Employeur</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $selectedService->employer->name }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($employees && $employees->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($employees as $employee)
                                @include('who-is-who::filament.components.employee-card', ['employee' => $employee])
                            @endforeach
                        </div>
                    @endif
                </x-filament::section>
            @endif
        </div>
    </div>
</x-filament-panels::page>
