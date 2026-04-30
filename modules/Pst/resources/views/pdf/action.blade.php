<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Action - {{ $action->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body class="bg-white text-gray-800">

<div class="max-w-4xl mx-auto px-6 py-8">

    {{-- Header --}}
    <header class="flex items-center justify-between border-b-2 border-blue-600 pb-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-blue-600">Plan Stratégique Transversal</h1>
            <p class="text-sm text-gray-500">Fiche Action</p>
        </div>
        <div class="text-right">
            @php $logo = public_path('images/Marche_logo.png'); @endphp
            @inlinedImage($logo)
        </div>
    </header>

    {{-- Action Title --}}
    <section class="mb-6">
        <h2 class="text-xl font-bold text-gray-900 bg-blue-50 px-4 py-3 rounded-lg border-l-4 border-blue-600">
            {{ $action->name }}
        </h2>
    </section>

    {{-- Status & Progress --}}
    <section class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">État d'avancement</p>
            <p class="text-lg font-semibold">
                @php
                    $stateColors = [
                        'START' => 'text-gray-600',
                        'PENDING' => 'text-green-600',
                        'FINISHED' => 'text-blue-600',
                        'SUSPENDED' => 'text-red-600',
                    ];
                    $color = $stateColors[$action->state->value] ?? 'text-gray-600';
                @endphp
                <span class="{{ $color }}">{{ $action->state->getLabel() }}</span>
            </p>
        </div>
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Pourcentage</p>
            <div class="flex items-center gap-3">
                <div class="flex-1 bg-gray-200 rounded-full h-3">
                    <div class="bg-blue-600 h-3 rounded-full" style="width: {{ $action->state_percentage ?? 0 }}%"></div>
                </div>
                <span class="text-lg font-semibold text-blue-600">{{ $action->state_percentage ?? 0 }}%</span>
            </div>
        </div>
    </section>

    {{-- Key Information --}}
    <section class="bg-white border border-gray-200 rounded-lg mb-6">
        <div class="grid grid-cols-2 divide-x divide-gray-200">
            <div class="p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Type</p>
                <p class="font-medium">{{ $action->type?->getLabel() ?? '-' }}</p>
            </div>
            <div class="p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Échéance</p>
                <p class="font-medium">{{ $action->due_date?->format('d/m/Y') ?? '-' }}</p>
            </div>
        </div>
        <div class="grid grid-cols-2 divide-x divide-gray-200 border-t border-gray-200">
            <div class="p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Feuille de route</p>
                <p class="font-medium">{{ $action->roadmap?->getLabel() ?? '-' }}</p>
            </div>
            <div class="p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Synergie</p>
                <p class="font-medium">{{ $action->synergy?->getLabel() ?? '-' }}</p>
            </div>
        </div>
        <div class="p-4 border-t border-gray-200">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Département</p>
            <p class="font-medium">{{ $action->department ?? '-' }}</p>
        </div>
    </section>

    {{-- Hierarchy / Context --}}
    @if($action->operationalObjective)
        <section class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-600 rounded"></span>
                Contexte stratégique
            </h3>
            <div class="bg-blue-50 rounded-lg p-4 space-y-2">
                @if($action->operationalObjective->strategicObjective)
                    <div>
                        <span class="text-xs font-semibold text-blue-600 uppercase">Objectif Stratégique</span>
                        <p class="font-medium text-gray-800">{{ $action->operationalObjective->strategicObjective->name }}</p>
                    </div>
                @endif
                <div>
                    <span class="text-xs font-semibold text-blue-600 uppercase">Objectif Opérationnel</span>
                    <p class="font-medium text-gray-800">{{ $action->operationalObjective->name }}</p>
                </div>
            </div>
        </section>
    @endif

    {{-- Description --}}
    @if($action->description)
        <section class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-600 rounded"></span>
                Description
            </h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $action->description }}</p>
            </div>
        </section>
    @endif

    {{-- Services --}}
    @php
        $leaderServices = $action->leaderServices;
        $partnerServices = $action->partnerServices;
    @endphp
    @if($leaderServices->isNotEmpty() || $partnerServices->isNotEmpty())
        <section class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-600 rounded"></span>
                Services
            </h3>
            <div class="grid grid-cols-2 gap-4">
                @if($leaderServices->isNotEmpty())
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-xs font-semibold text-green-700 uppercase mb-2">Services pilotes</p>
                        <ul class="space-y-1">
                            @foreach($leaderServices as $service)
                                <li class="text-gray-800">{{ $service->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if($partnerServices->isNotEmpty())
                    <div class="bg-amber-50 rounded-lg p-4">
                        <p class="text-xs font-semibold text-amber-700 uppercase mb-2">Services partenaires</p>
                        <ul class="space-y-1">
                            @foreach($partnerServices as $service)
                                <li class="text-gray-800">{{ $service->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Agents --}}
    @php $agents = $action->users; @endphp
    @if($agents->isNotEmpty())
        <section class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-600 rounded"></span>
                Agents pilotes
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($agents as $agent)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        {{ $agent->first_name }} {{ $agent->last_name }}
                    </span>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Mandataires --}}
    @php $mandataries = $action->mandataries; @endphp
    @if($mandataries->isNotEmpty())
        <section class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-600 rounded"></span>
                Mandataires
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($mandataries as $mandatary)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                        {{ $mandatary->first_name }} {{ $mandatary->last_name }}
                    </span>
                @endforeach
            </div>
        </section>
    @endif

    {{-- External Partners --}}
    @php $partners = $action->partners; @endphp
    @if($partners->isNotEmpty())
        <section class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-600 rounded"></span>
                Partenaires externes
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($partners as $partner)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-800">
                        {{ $partner->name }}
                    </span>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ODDs (Sustainable Development Goals) --}}
    @php $odds = $action->odds; @endphp
    @if($odds->isNotEmpty())
        <section class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-600 rounded"></span>
                Objectifs de développement durable
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($odds as $odd)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                          style="background-color: {{ $odd->color ?? '#e5e7eb' }}; color: white;">
                        {{ $odd->name }}
                    </span>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Linked Actions --}}
    @php $linkedActions = $action->linkedActions; @endphp
    @if($linkedActions->isNotEmpty())
        <section class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-600 rounded"></span>
                Actions liées
            </h3>
            <ul class="bg-gray-50 rounded-lg divide-y divide-gray-200">
                @foreach($linkedActions as $linked)
                    <li class="px-4 py-3 text-gray-700">{{ $linked->name }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Evaluation & Planning --}}
    @if($action->evaluation_indicator || $action->work_plan)
        <section class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-600 rounded"></span>
                Évaluation & Planification
            </h3>
            <div class="space-y-4">
                @if($action->evaluation_indicator)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Indicateur d'évaluation</p>
                        <p class="text-gray-700 whitespace-pre-line">{{ $action->evaluation_indicator }}</p>
                    </div>
                @endif
                @if($action->work_plan)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Plan de travail</p>
                        <p class="text-gray-700 whitespace-pre-line">{{ $action->work_plan }}</p>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Budget --}}
    @if($action->budget_estimate || $action->financing_mode)
        <section class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-600 rounded"></span>
                Budget & Financement
            </h3>
            <div class="grid grid-cols-2 gap-4">
                @if($action->budget_estimate)
                    <div class="bg-emerald-50 rounded-lg p-4">
                        <p class="text-xs font-semibold text-emerald-700 uppercase mb-1">Estimation budgétaire</p>
                        <p class="text-lg font-bold text-emerald-800">{{ $action->budget_estimate }}</p>
                    </div>
                @endif
                @if($action->financing_mode)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Mode de financement</p>
                        <p class="text-gray-700">{{ $action->financing_mode }}</p>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Follow-ups --}}
    @php $followUps = $action->followUps; @endphp
    @if($followUps->isNotEmpty())
        <section class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-600 rounded"></span>
                Suivis
            </h3>
            <div class="space-y-3">
                @foreach($followUps as $followUp)
                    <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-blue-400">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-xs font-semibold text-gray-500">
                                {{ $followUp->created_at?->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <p class="text-gray-700 whitespace-pre-line">{{ $followUp->content ?? $followUp->note ?? $followUp->description ?? '-' }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Notes --}}
    @if($action->note)
        <section class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-600 rounded"></span>
                Notes
            </h3>
            <div class="bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-400">
                <p class="text-gray-700 whitespace-pre-line">{{ $action->note }}</p>
            </div>
        </section>
    @endif

    {{-- Footer --}}
    <footer class="border-t border-gray-200 pt-4 mt-8 text-center text-xs text-gray-500">
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
        <p class="mt-1">Plan Stratégique Transversal - Ville de Marche-en-Famenne</p>
    </footer>

</div>

</body>
</html>
