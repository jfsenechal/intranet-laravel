@php
    /** @var \AcMarche\Hrm\Models\Employee $employee */
    $fullName = mb_trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
    $jobTitles = $employee->activeContracts
        ->pluck('job_title')
        ->filter()
        ->unique()
        ->values();
    $services = $employee->activeContracts
        ->pluck('service.name')
        ->filter()
        ->unique()
        ->values();
    $photoUrl = $employee->photo
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($employee->photo)
        : 'https://ui-avatars.com/api/?size=160&name=' . urlencode($fullName !== '' ? $fullName : '?');
    $phoneDisplay = $employee->professional_phone
        ? trim($employee->professional_phone . ($employee->professional_phone_extension ? ' (ext. ' . $employee->professional_phone_extension . ')' : ''))
        : null;
    $hasContactInfo = $employee->professional_email
        || $phoneDisplay
        || $employee->professional_mobile;
@endphp

<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm flex gap-4">
    <img src="{{ $photoUrl }}"
         alt="{{ $fullName }}"
         class="h-24 w-24 rounded-full object-cover shrink-0 bg-gray-100 dark:bg-gray-800" />

    <div class="flex-1 min-w-0">
        <div class="text-base font-semibold text-gray-900 dark:text-gray-100 truncate">
            {{ $employee->last_name }} {{ $employee->first_name }}
        </div>

        @if ($jobTitles->isNotEmpty())
            <div class="text-gray-700 dark:text-gray-300 mt-0.5">
                {{ $jobTitles->implode(', ') }}
            </div>
        @endif

        @if ($services->isNotEmpty())
            <div class="text-primary-600 dark:text-primary-400 mt-0.5">
                {{ $services->implode(', ') }}
            </div>
        @endif

        @if ($hasContactInfo)
            <div class="mt-3">
                <x-filament::modal
                    :id="'employee-contact-' . $employee->id"
                    icon="heroicon-o-identification"
                    width="md"
                >
                    <x-slot name="trigger">
                        <x-filament::button
                            size="xs"
                            color="gray"
                            icon="heroicon-o-identification"
                        >
                            Contact
                        </x-filament::button>
                    </x-slot>

                    <x-slot name="heading">
                        {{ $fullName }}
                    </x-slot>

                    @if ($jobTitles->isNotEmpty() || $services->isNotEmpty())
                        <x-slot name="description">
                            @if ($jobTitles->isNotEmpty())
                                {{ $jobTitles->implode(', ') }}
                            @endif
                            @if ($services->isNotEmpty())
                                <span class="block text-primary-600 dark:text-primary-400">
                                    {{ $services->implode(', ') }}
                                </span>
                            @endif
                        </x-slot>
                    @endif

                    <div class="space-y-3">
                        @if ($employee->professional_email)
                            <div class="flex items-start gap-2">
                                <x-filament::icon icon="heroicon-o-envelope" class="h-5 w-5 mt-0.5 text-gray-500 flex-shrink-0" />
                                <div class="min-w-0">
                                    <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</div>
                                    <a href="mailto:{{ $employee->professional_email }}"
                                       class="text-primary-600 dark:text-primary-400 hover:underline break-all">
                                        {{ $employee->professional_email }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if ($phoneDisplay)
                            <div class="flex items-start gap-2">
                                <x-filament::icon icon="heroicon-o-phone" class="h-5 w-5 mt-0.5 text-gray-500 flex-shrink-0" />
                                <div>
                                    <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Téléphone</div>
                                    <a href="tel:{{ $employee->professional_phone }}"
                                       class="text-primary-600 dark:text-primary-400 hover:underline">
                                        {{ $employee->professional_phone }}
                                    </a>
                                    @if ($employee->professional_phone_extension)
                                        <span class="text-gray-600 dark:text-gray-300">
                                            (ext. {{ $employee->professional_phone_extension }})
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ($employee->professional_mobile)
                            <div class="flex items-start gap-2">
                                <x-filament::icon icon="heroicon-o-device-phone-mobile" class="h-5 w-5 mt-0.5 text-gray-500 flex-shrink-0" />
                                <div>
                                    <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">GSM</div>
                                    <a href="tel:{{ $employee->professional_mobile }}"
                                       class="text-primary-600 dark:text-primary-400 hover:underline">
                                        {{ $employee->professional_mobile }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-filament::modal>
            </div>
        @endif
    </div>
</div>
