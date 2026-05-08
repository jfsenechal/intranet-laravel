<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche agent - {{ $employee->last_name }} {{ $employee->first_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; }
        html { -webkit-print-color-adjust: exact; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body class="bg-white text-gray-800">
<div class="max-w-4xl mx-auto px-6 py-8">

    {{-- Header --}}
    <header class="flex items-center justify-between border-b-2 border-blue-700 pb-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-blue-700">{{ $employee->last_name }} {{ $employee->first_name }}</h1>
            <p class="text-sm text-gray-500">Fiche agent</p>
        </div>
        <div class="text-right text-xs text-gray-400">
            <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
            @if($employee->status)
                <span class="inline-block mt-1 px-2 py-0.5 rounded bg-blue-100 text-blue-700 font-semibold">
                    {{ $employee->status->getLabel() }}
                </span>
            @endif
        </div>
    </header>

    {{-- Personal Information --}}
    <section class="mb-6">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
            <span class="w-1 h-4 bg-blue-700 rounded"></span>
            Informations personnelles
        </h2>
        <div class="bg-gray-50 rounded-lg border border-gray-200">
            <div class="grid grid-cols-2 divide-x divide-gray-200">
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Civilité</p>
                    <p class="font-medium">{{ $employee->civility ?? '—' }}</p>
                </div>
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Date de naissance</p>
                    <p class="font-medium">{{ $employee->birth_date?->format('d/m/Y') ?? '—' }}</p>
                </div>
            </div>
            <div class="p-3 border-t border-gray-200">
                <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Adresse</p>
                <p class="font-medium">{{ trim(($employee->address ?? '').' '.($employee->postal_code ?? '').' '.($employee->city ?? '')) ?: '—' }}</p>
            </div>
            <div class="grid grid-cols-2 divide-x divide-gray-200 border-t border-gray-200">
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Coordonnées privées</p>
                    @if($employee->private_email)
                        <p class="text-sm">✉ {{ $employee->private_email }}</p>
                    @endif
                    @if($employee->private_phone)
                        <p class="text-sm">☎ {{ $employee->private_phone }}</p>
                    @endif
                    @if($employee->private_mobile)
                        <p class="text-sm">📱 {{ $employee->private_mobile }}</p>
                    @endif
                    @if(!$employee->private_email && !$employee->private_phone && !$employee->private_mobile)
                        <p class="text-sm text-gray-400">—</p>
                    @endif
                </div>
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Coordonnées professionnelles</p>
                    @if($employee->professional_email)
                        <p class="text-sm">✉ {{ $employee->professional_email }}</p>
                    @endif
                    @if($employee->professional_phone)
                        <p class="text-sm">☎ {{ $employee->professional_phone }}{{ $employee->professional_phone_extension ? ' (ext. '.$employee->professional_phone_extension.')' : '' }}</p>
                    @endif
                    @if($employee->professional_mobile)
                        <p class="text-sm">📱 {{ $employee->professional_mobile }}</p>
                    @endif
                    @if(!$employee->professional_email && !$employee->professional_phone && !$employee->professional_mobile)
                        <p class="text-sm text-gray-400">—</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Employment Information --}}
    <section class="mb-6">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
            <span class="w-1 h-4 bg-blue-700 rounded"></span>
            Emploi
        </h2>
        <div class="bg-gray-50 rounded-lg border border-gray-200">
            <div class="grid grid-cols-3 divide-x divide-gray-200">
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Date d'entrée</p>
                    <p class="font-medium">{{ $employee->hired_at?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Date de sortie</p>
                    <p class="font-medium">{{ $employee->left_at?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Date de rappel</p>
                    <p class="font-medium">{{ $employee->reminder_date?->format('d/m/Y') ?? '—' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 divide-x divide-gray-200 border-t border-gray-200">
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Anc. pécuniaire</p>
                    <p class="font-medium">{{ $employee->salary_seniority_date?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Anc. d'échelle</p>
                    <p class="font-medium">{{ $employee->scale_seniority_date?->format('d/m/Y') ?? '—' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-3 divide-x divide-gray-200 border-t border-gray-200">
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Échelle</p>
                    <p class="font-medium">{{ $employee->payScale?->name ?? '—' }}</p>
                </div>
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Code barème</p>
                    <p class="font-medium">{{ $employee->pay_scale_code ?? '—' }}</p>
                </div>
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Unité locale</p>
                    <p class="font-medium">{{ $employee->local_unit ?? '—' }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Contracts --}}
    @if(in_array('contracts', $selectedRelations) && $employee->contracts->isNotEmpty())
        <section class="mb-6">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-green-600 rounded"></span>
                Contrats ({{ $employee->contracts->count() }})
            </h2>
            <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Début</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Fin</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Fonction</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Nature</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Service</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($employee->contracts as $contract)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                            <td class="px-3 py-2">{{ $contract->start_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $contract->end_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $contract->job_title ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $contract->contractNature?->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $contract->service?->name ?? $contract->direction?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    {{-- Absences --}}
    @if(in_array('absences', $selectedRelations) && $employee->absences->isNotEmpty())
        <section class="mb-6">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-orange-500 rounded"></span>
                Absences ({{ $employee->absences->count() }})
            </h2>
            <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Début</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Fin</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Motif</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Clôturé</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($employee->absences as $absence)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                            <td class="px-3 py-2">{{ $absence->start_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $absence->end_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $absence->reason?->getLabel() ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $absence->is_closed ? 'Oui' : 'Non' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    {{-- Trainings --}}
    @if(in_array('trainings', $selectedRelations) && $employee->trainings->isNotEmpty())
        <section class="mb-6">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-purple-600 rounded"></span>
                Formations ({{ $employee->trainings->count() }})
            </h2>
            <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Intitulé</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Type</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Date</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Durée</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($employee->trainings as $training)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                            <td class="px-3 py-2">{{ $training->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $training->training_type?->getLabel() ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $training->start_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-2">{{ \AcMarche\Hrm\Models\Training::formatDuration($training->duration_minutes) ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    {{-- Evaluations --}}
    @if(in_array('evaluations', $selectedRelations) && $employee->evaluations->isNotEmpty())
        <section class="mb-6">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-yellow-500 rounded"></span>
                Évaluations ({{ $employee->evaluations->count() }})
            </h2>
            <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Date</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Résultat</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Direction</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Prochaine</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($employee->evaluations as $evaluation)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                            <td class="px-3 py-2">{{ $evaluation->evaluation_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $evaluation->result?->getLabel() ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $evaluation->direction?->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $evaluation->next_evaluation_date?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    {{-- Diplomas --}}
    @if(in_array('diplomas', $selectedRelations) && $employee->diplomas->isNotEmpty())
        <section class="mb-6">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-indigo-600 rounded"></span>
                Diplômes ({{ $employee->diplomas->count() }})
            </h2>
            <ul class="bg-gray-50 rounded-lg border border-gray-200 divide-y divide-gray-200">
                @foreach($employee->diplomas as $diploma)
                    <li class="px-4 py-3 text-gray-700">{{ $diploma->name ?? '—' }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Internships --}}
    @if(in_array('internships', $selectedRelations) && $employee->internships->isNotEmpty())
        <section class="mb-6">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-teal-600 rounded"></span>
                Stages ({{ $employee->internships->count() }})
            </h2>
            <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Début</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Fin</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($employee->internships as $internship)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                            <td class="px-3 py-2">{{ $internship->start_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $internship->end_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $internship->notes ?? $internship->note ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    {{-- Valorizations --}}
    @if(in_array('valorizations', $selectedRelations) && $employee->valorizations->isNotEmpty())
        <section class="mb-6">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-rose-600 rounded"></span>
                Valorisations ({{ $employee->valorizations->count() }})
            </h2>
            <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Employeur</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Durée</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Régime</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Contenu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($employee->valorizations as $valorization)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                            <td class="px-3 py-2">{{ $valorization->employer_name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $valorization->duration ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $valorization->regime ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $valorization->content ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    {{-- Deadlines --}}
    @if(in_array('deadlines', $selectedRelations) && $employee->deadlines->isNotEmpty())
        <section class="mb-6">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-red-600 rounded"></span>
                Échéances ({{ $employee->deadlines->count() }})
            </h2>
            <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Intitulé</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Début</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Fin</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Clôturé</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($employee->deadlines as $deadline)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                            <td class="px-3 py-2">{{ $deadline->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $deadline->start_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $deadline->end_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $deadline->is_closed ? 'Oui' : 'Non' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    {{-- Applications --}}
    @if(in_array('applications', $selectedRelations) && $employee->applications->isNotEmpty())
        <section class="mb-6">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-sky-600 rounded"></span>
                Candidatures ({{ $employee->applications->count() }})
            </h2>
            <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Reçue le</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($employee->applications as $application)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                            <td class="px-3 py-2">{{ $application->received_at?->format('d/m/Y') ?? $application->created_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $application->notes ?? $application->note ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    {{-- Documents --}}
    @if(in_array('documents', $selectedRelations) && $employee->documents->isNotEmpty())
        <section class="mb-6">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-gray-600 rounded"></span>
                Documents ({{ $employee->documents->count() }})
            </h2>
            <ul class="bg-gray-50 rounded-lg border border-gray-200 divide-y divide-gray-200">
                @foreach($employee->documents as $document)
                    <li class="px-4 py-3 flex items-center justify-between">
                        <span class="text-gray-700">{{ $document->name ?? $document->file_name ?? '—' }}</span>
                        @if($document->created_at)
                            <span class="text-xs text-gray-400">{{ $document->created_at->format('d/m/Y') }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Notes --}}
    @if($employee->notes)
        <section class="mb-6">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-yellow-400 rounded"></span>
                Remarques
            </h2>
            <div class="bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-400">
                <p class="text-gray-700 whitespace-pre-line">{!! strip_tags($employee->notes) !!}</p>
            </div>
        </section>
    @endif

    {{-- Footer --}}
    <footer class="border-t border-gray-200 pt-4 mt-8 text-center text-xs text-gray-500">
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
        <p class="mt-1">Module RH</p>
    </footer>

</div>
</body>
</html>
