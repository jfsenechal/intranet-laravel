<h4 class="text-green-600 font-semibold mb-2">Détails des courses</h4>
<p class="text-yellow-600 mb-4">Tarif de l'exercice : {{ number_format($rate, 4, ',', '.') }} €</p>
<table class="w-full border border-gray-300 border-collapse">
    <thead>
        <tr class="bg-gray-100">
            <th class="border border-gray-300 p-2 text-left">Date</th>
            <th class="border border-gray-300 p-2 text-left">Motif de déplacement</th>
            <th class="border border-gray-300 p-2 text-left">Distance</th>
            <th class="border border-gray-300 p-2 text-left">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($declaration->trips as $trip)
            <tr class="hover:bg-gray-50">
                <td class="border border-gray-300 p-2">{{ $trip->departure_date?->format('d-m-Y') }}</td>
                <td class="border border-gray-300 p-2">{{ Str::limit($trip->content, 50) }}</td>
                <td class="border border-gray-300 p-2">{{ $trip->distance }}</td>
                <td class="border border-gray-300 p-2">
                    {{ number_format($trip->distance * $rate, 2, ',', '.') }} €
                </td>
            </tr>
        @endforeach
        <tr class="bg-gray-100 font-semibold">
            <td class="border border-gray-300 p-2">Sous Total</td>
            <td class="border border-gray-300 p-2"></td>
            <td class="border border-gray-300 p-2">{{ $declarationSummary->totalKilometers }} Km</td>
            <td class="border border-gray-300 p-2">{{ number_format($declarationSummary->totalMileageAllowance, 2, ',', '.') }} €</td>
        </tr>
        <tr class="hover:bg-gray-50">
            <td class="border border-gray-300 p-2">
                <strong>Retenue Omnium :</strong>
                @if($declaration->omnium)
                    <span class="text-green-600">Oui</span>
                @else
                    <span class="text-red-600">Non</span>
                @endif
            </td>
            <td class="border border-gray-300 p-2">
                @if($declaration->omnium)
                    - {{ $declaration->rate_omnium }} €
                @endif
            </td>
            <td class="border border-gray-300 p-2">
                @if($declaration->omnium)
                    {{ $declarationSummary->totalKilometers }}
                @endif
            </td>
            <td class="border border-gray-300 p-2">{{ number_format($declarationSummary->totalOmnium, 2, ',', '.') }} €</td>
        </tr>
        <tr class="bg-gray-100 font-bold">
            <td class="border border-gray-300 p-2">TOTAL A REMBOURSER</td>
            <td class="border border-gray-300 p-2" colspan="2"></td>
            <td class="border border-gray-300 p-2 text-green-700">{{ number_format($declarationSummary->totalRefund, 2, ',', '.') }} €</td>
        </tr>
    </tbody>
</table>
