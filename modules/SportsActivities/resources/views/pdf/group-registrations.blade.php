<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $group->activity?->name }} - {{ $group->name() }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; }
        html { -webkit-print-color-adjust: exact; }
    </style>
</head>
<body class="bg-white text-gray-800">

<header class="flex items-center justify-between border-b-2 border-emerald-600 pb-4 mb-6">
    <div>
        @php $logo = public_path('images/Marche_logo.png'); @endphp
        @inlinedImage($logo)
    </div>
    <div class="text-right">
        <h1 class="text-xl font-bold text-emerald-700">{{ $group->activity?->name }}</h1>
        <p class="text-sm text-gray-600">{{ $group->name() }}</p>
    </div>
</header>

@if($group->registrations->isNotEmpty())
    <table class="w-full text-sm border border-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Nom</th>
                <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Adresse</th>
                <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Téléphone ou gsm</th>
                <th class="text-left px-3 py-2 text-xs font-semibold text-gray-600 uppercase">Né le</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($group->registrations as $registration)
                @php $member = $registration->member; @endphp
                <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                    <td class="px-3 py-2">{{ $member?->last_name }} {{ $member?->first_name }}</td>
                    <td class="px-3 py-2">{{ trim(($member?->street ?? '').' '.($member?->postal_code ?? '').' '.($member?->city ?? '')) ?: '—' }}</td>
                    <td class="px-3 py-2">
                        {{ trim(($member?->phone ?? '').' '.($member?->mobile ?? '')) ?: '—' }}
                        @if($member?->email)
                            <br>{{ $member->email }}
                        @endif
                    </td>
                    <td class="px-3 py-2">{{ $member?->birth_date?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p class="text-orange-600">Aucun sportif inscrit</p>
@endif

</body>
</html>
