<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résumé de la fiche - {{ $profile->fullName() }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; }
        html { -webkit-print-color-adjust: exact; }
    </style>
</head>
<body class="bg-white text-gray-800">
<div class="max-w-4xl mx-auto px-6 py-8">

    <header class="flex items-center justify-between border-b-2 border-blue-700 pb-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-blue-700">{{ $profile->fullName() }}</h1>
            <p class="text-sm text-gray-500">Résumé de la fiche informatique</p>
        </div>
        <div class="text-right text-xs text-gray-400">
            <p>Généré le {{ display_datetime(now(), 'd/m/Y à H:i') }}</p>
        </div>
    </header>

    <section class="mb-6">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
            <span class="w-1 h-4 bg-blue-700 rounded"></span>
            Coordonnées
        </h2>
        <div class="bg-gray-50 rounded-lg border border-gray-200">
            <div class="grid grid-cols-2 divide-x divide-gray-200">
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Identifiant</p>
                    <p class="font-medium">{{ $profile->username ?? '—' }}</p>
                </div>
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Adresse mail</p>
                    <p class="font-medium">{{ $email ?? '—' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 divide-x divide-gray-200 border-t border-gray-200">
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Matricule RH</p>
                    <p class="font-medium">{{ $profile->employee_id ?? '—' }}</p>
                </div>
                <div class="p-3">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-0.5">Emplacement</p>
                    <p class="font-medium">{{ $profile->location ?: '—' }}</p>
                </div>
            </div>
            @if($employee && $employee->activeContracts->isNotEmpty())
                <div class="p-3 border-t border-gray-200">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Affectation</p>
                    @foreach($employee->activeContracts as $contract)
                        <p class="text-sm">
                            {{ $contract->direction?->name ?? '—' }}
                            @if($contract->service) &bull; {{ $contract->service->name }} @endif
                            @if($contract->job_title) <span class="text-gray-500">({{ $contract->job_title }})</span> @endif
                        </p>
                    @endforeach
                </div>
            @endif
            @if(! empty($profile->supervisors))
                <div class="p-3 border-t border-gray-200">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Responsable(s)</p>
                    <p class="text-sm">{{ implode(', ', $profile->supervisors) }}</p>
                </div>
            @endif
            @if($profile->notes)
                <div class="p-3 border-t border-gray-200">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Remarques</p>
                    <p class="text-sm whitespace-pre-wrap">{{ $profile->notes }}</p>
                </div>
            @endif
        </div>
    </section>

    <section class="mb-6">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
            <span class="w-1 h-4 bg-blue-700 rounded"></span>
            Mailboxes partagées
        </h2>
        @if(! empty($profile->emails))
            <ul class="list-disc list-inside text-sm bg-gray-50 rounded-lg border border-gray-200 p-3">
                @foreach($profile->emails as $sharedEmail)
                    <li>{{ $sharedEmail }}</li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">Aucune</p>
        @endif
    </section>

    <section class="mb-6">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
            <span class="w-1 h-4 bg-blue-700 rounded"></span>
            Dossiers sur le serveur
        </h2>
        @if($folderPaths !== [])
            <ul class="list-disc list-inside text-sm bg-gray-50 rounded-lg border border-gray-200 p-3">
                @foreach($folderPaths as $path)
                    <li>{{ $path }}</li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">Aucun</p>
        @endif
    </section>

    <section class="mb-6">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
            <span class="w-1 h-4 bg-blue-700 rounded"></span>
            Applications externes
        </h2>
        @if($profile->externalApplications->isNotEmpty())
            <ul class="list-disc list-inside text-sm bg-gray-50 rounded-lg border border-gray-200 p-3">
                @foreach($profile->externalApplications as $application)
                    <li>{{ $application->name }}</li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">Aucune</p>
        @endif
    </section>

    <section class="mb-6">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
            <span class="w-1 h-4 bg-blue-700 rounded"></span>
            Modules de l'intranet
        </h2>
        @if($modules->isNotEmpty())
            <ul class="list-disc list-inside text-sm bg-gray-50 rounded-lg border border-gray-200 p-3">
                @foreach($modules as $module)
                    <li>{{ $module->name }}</li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">Aucun</p>
        @endif
    </section>

    <section class="mb-6">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
            <span class="w-1 h-4 bg-blue-700 rounded"></span>
            Matériel
        </h2>
        @if($profile->hardware)
            <div class="grid grid-cols-2 gap-3 text-sm bg-gray-50 rounded-lg border border-gray-200 p-3">
                <p><span class="text-gray-500">PC existant :</span> {{ $profile->hardware->existing_pc ?: '—' }}</p>
                <p><span class="text-gray-500">Nouveau PC :</span> {{ $profile->hardware->new_pc ?: '—' }}</p>
                <p><span class="text-gray-500">VPN :</span> {{ $profile->hardware->vpn ? 'Oui' : 'Non' }}</p>
                <p><span class="text-gray-500">Autre :</span> {{ $profile->hardware->other ?: '—' }}</p>
            </div>
        @else
            <p class="text-sm text-gray-500">Aucun</p>
        @endif
    </section>

    <section>
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
            <span class="w-1 h-4 bg-blue-700 rounded"></span>
            Téléphonie
        </h2>
        @if($profile->phone)
            <div class="grid grid-cols-2 gap-3 text-sm bg-gray-50 rounded-lg border border-gray-200 p-3">
                <p><span class="text-gray-500">Numéro :</span> {{ $profile->phone->existing_number ?: '—' }}</p>
                <p><span class="text-gray-500">Mobile :</span> {{ $profile->phone->mobile_number ?: '—' }}</p>
                <p><span class="text-gray-500">Nouveau numéro :</span> {{ $profile->phone->new_number ? 'Oui' : 'Non' }}</p>
                <p><span class="text-gray-500">Numéro direct :</span> {{ $profile->phone->external_number ? 'Oui' : 'Non' }}</p>
            </div>
        @else
            <p class="text-sm text-gray-500">Aucune</p>
        @endif
    </section>

</div>
</body>
</html>
