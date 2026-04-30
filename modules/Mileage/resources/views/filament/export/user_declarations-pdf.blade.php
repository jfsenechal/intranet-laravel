<html lang="fr">
<head>
    <title>Déclaration de déplacement - {{ $username }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .page-breaker {
            page-break-after: always;
        }
    </style>
</head>
<body class="p-4">
<div class="container mx-auto">

    <h1 class="text-2xl font-bold mb-4">
        @if($declaration)
            {{ strtoupper($declaration->last_name) }} {{ $declaration->first_name }}
        @else
            {{ $username }}
        @endif
    </h1>

    @if($declaration)
        <div class="mb-4">
            <p>{{ $declaration->street }}</p>
            <p>{{ $declaration->postal_code }} {{ $declaration->city }}</p>
            <p><strong>Numéro de compte :</strong> {{ $declaration->iban }}</p>
            <p>
                <strong>Omnium :</strong>
                @if($declaration->omnium)
                    Oui
                @else
                    Non
                @endif
            </p>
            @if($declaration->college_date)
                <p><strong>Délibé Collège :</strong> {{ $declaration->college_date->format('d-m-Y') }}</p>
            @endif
        </div>
    @endif

    <table class="w-full border-collapse border border-gray-300 text-sm">
        <thead>
        <tr class="bg-gray-100">
            <th class="border border-gray-300 px-2 py-1">Année</th>
            <th class="border border-gray-300 px-2 py-1">Type</th>
            @foreach($months as $month)
                <th class="border border-gray-300 px-2 py-1">{{ $month }}</th>
            @endforeach
            <th class="border border-gray-300 px-2 py-1">Total</th>
        </tr>
        </thead>
        <tbody>

        @php
            $internes = $deplacements['interne'];
            $externes = $deplacements['externe'];
        @endphp

        @foreach($years as $year)
            @php
                $totalInterne = 0;
                $totalExterne = 0;
            @endphp
            <tr>
                <td class="border border-gray-300 px-2 py-1 font-semibold">{{ $year }}</td>
                <td class="border border-gray-300 px-2 py-1">Interne</td>
                @foreach(array_keys($months) as $nummonth)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        @if(isset($internes[$year][$nummonth]))
                            {{ $internes[$year][$nummonth] }}
                            @php $totalInterne += $internes[$year][$nummonth]; @endphp
                        @endif
                    </td>
                @endforeach
                <td class="border border-gray-300 px-2 py-1 text-right font-semibold">{{ $totalInterne }}</td>
            </tr>
            <tr>
                <td class="border border-gray-300 px-2 py-1"></td>
                <td class="border border-gray-300 px-2 py-1">Externe</td>
                @foreach(array_keys($months) as $nummonth)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        @if(isset($externes[$year][$nummonth]))
                            {{ $externes[$year][$nummonth] }}
                            @php $totalExterne += $externes[$year][$nummonth]; @endphp
                        @endif
                    </td>
                @endforeach
                <td class="border border-gray-300 px-2 py-1 text-right font-semibold">{{ $totalExterne }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="page-breaker"></div>
</div>
</body>
</html>
