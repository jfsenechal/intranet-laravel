<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Selectionner la date
        </x-slot>

        <form wire:submit.prevent="loadPreviewData">
            {{ $this->form }}
        </form>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            Courriers a notifier ({{ $this->getTableRecords()->count() }})
        </x-slot>

        {{ $this->table }}
    </x-filament::section>

    @if(!empty($previewData))
    <x-filament::section>
        <x-slot name="heading">
            Apercu des notifications ({{ count($previewData) }} destinataires)
        </x-slot>

        <div class="space-y-4">
            @foreach($previewData as $data)
                <x-filament::section collapsible collapsed>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <span>{{ $data['recipient']->first_name }} {{ $data['recipient']->last_name }}</span>
                            <span class="text-sm text-gray-500">({{ $data['recipient']->email }})</span>
                            <x-filament::badge>
                                {{ $data['mails']->count() }} courrier(s)
                            </x-filament::badge>
                            @if($data['has_index_role'])
                                <x-filament::badge color="info">
                                    Role Index
                                </x-filament::badge>
                            @endif
                            @if($data['recipient']->receives_attachments)
                                <x-filament::badge color="success">
                                    Recoit les PJ
                                </x-filament::badge>
                            @endif
                        </div>
                    </x-slot>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Numero</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Expediteur</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Description</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Original a</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Copie a</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Recomm / Accuse</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($data['mails'] as $courrier)
                                <tr>
                                    <td class="px-4 py-2">{{ $courrier->reference_number }}</td>
                                    <td class="px-4 py-2">{{ $courrier->sender }}</td>
                                    <td class="px-4 py-2">{{ Str::limit($courrier->description, 50) }}</td>
                                    <td class="px-4 py-2">
                                        @php
                                            $primaryServices = $courrier->services->where('pivot.is_primary', true)->pluck('name');
                                            $primaryRecipients = $courrier->recipients->where('pivot.is_primary', true)->map(fn($r) => $r->first_name . ' ' . $r->last_name);
                                        @endphp
                                        {{ $primaryServices->merge($primaryRecipients)->implode(', ') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        @php
                                            $secondaryServices = $courrier->services->where('pivot.is_primary', false)->pluck('name');
                                            $secondaryRecipients = $courrier->recipients->where('pivot.is_primary', false)->map(fn($r) => $r->first_name . ' ' . $r->last_name);
                                        @endphp
                                        {{ $secondaryServices->merge($secondaryRecipients)->implode(', ') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($courrier->is_registered)
                                            <x-filament::badge color="warning">Recommande</x-filament::badge>
                                        @endif
                                        @if($courrier->has_acknowledgment)
                                            <x-filament::badge color="info">Accuse</x-filament::badge>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>
            @endforeach
        </div>
    </x-filament::section>
    @else
        <x-filament::section>
            <div class="text-center py-8 text-gray-500">
                <x-filament::icon icon="tabler-inbox-off" class="mx-auto h-12 w-12 mb-4 text-gray-400" />
                <p>Aucun courrier non notifie pour cette date.</p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
