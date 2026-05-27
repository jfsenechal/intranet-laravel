<x-filament-panels::page>
    @php
        $issues = $this->getIssues();
    @endphp

    <x-filament::section>
        <x-slot name="heading">Résultat de la vérification</x-slot>
        <x-slot name="description">
            Vérification de la correspondance entre les profils informatiques et la table des employés (HRM)
            sur base de l'attribut <code>employee_id</code> et des nom et prénom.
        </x-slot>

        @if ($issues === [])
            <div class="flex items-center gap-2 text-success-600 dark:text-success-400">
                <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5"/>
                <span>Tous les profils sont cohérents avec la table HRM.</span>
            </div>
        @else
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                {{ count($issues) }} profil(s) présentent une anomalie.
            </p>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Profil</th>
                            <th class="px-3 py-2 text-left font-semibold">Nom / Prénom (profil)</th>
                            <th class="px-3 py-2 text-left font-semibold">employee_id</th>
                            <th class="px-3 py-2 text-left font-semibold">Nom / Prénom (HRM)</th>
                            <th class="px-3 py-2 text-left font-semibold">Anomalie</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($issues as $row)
                            @php
                                $profile = $row['profile'];
                                $employee = $row['employee'];
                            @endphp
                            <tr>
                                <td class="px-3 py-2">
                                    <x-filament::link
                                        :href="\AcMarche\Agent\Filament\Resources\Profiles\ProfileResource::getUrl('view', ['record' => $profile])"
                                    >
                                        {{ $profile->username ?? $profile->id }}
                                    </x-filament::link>
                                </td>
                                <td class="px-3 py-2">{{ $profile->last_name }} {{ $profile->first_name }}</td>
                                <td class="px-3 py-2">{{ $profile->employee_id ?? '—' }}</td>
                                <td class="px-3 py-2">
                                    @if ($employee)
                                        {{ $employee->last_name }} {{ $employee->first_name }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-danger-600 dark:text-danger-400">{{ $row['issue'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
