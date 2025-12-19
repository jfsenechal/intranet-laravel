<h4 class="text-success">Détails des courses</h4>
<p class="text-warning">Tarif de l'exercice : {{ number_format($rate, 4, ',', '.') }} € </p>
<table class="table table-bordered table-hover">
    <tr class="active">
        <th>Date</th>
        <th>Motif de déplacement</th>
        <th>Distance</th>
        <th>Total</th>
    </tr>
    @foreach($declaration->trips as $trip)
        <tr>
            <td>{{ $trip->departure_date?->format('d-m-Y') }}</td>
            <td>{{ Str::limit($trip->content, 50) }}</td>
            <td>{{ $trip->distance }}</td>
            <td>
                {{ number_format($trip->distance * $rate, 2, ',', '.') }} €
            </td>
        </tr>
    @endforeach
    <tr class="active">
        <td><strong>Sous Total</strong></td>
        <td></td>
        <td><strong>{{ $declarationSummary->totalKilometers }} Km</strong></td>
        <td>{{ number_format($declarationSummary->totalMileageAllowance, 2, ',', '.') }} €</td>
    </tr>
    <tr>
        <td><strong>Retenue Omnium :</strong>
            @if($declaration->omnium)
                Oui
            @else
                Non
            @endif
        </td>
        <td>
            @if($declaration->omnium)
                - {{ $declaration->rate_omnium }} €
            @endif
        </td>
        <td>
            @if($declaration->omnium)
                {{ $declarationSummary->totalKilometers }}
            @endif
        </td>
        <td>{{ number_format($declarationSummary->totalOmnium, 2, ',', '.') }} €</td>
    </tr>
    <tr class="active">
        <td><strong>TOTAL A REMBOURSER</strong></td>
        <td colspan="2"></td>
        <td>{{ number_format($declarationSummary->totalRefund, 2, ',', '.') }} €</td>
    </tr>
</table>
