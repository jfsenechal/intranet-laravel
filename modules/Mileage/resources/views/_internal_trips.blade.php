<h4 class="text-success">Détails des courses</h4>
<p class="text-warning">Tarif de l'exercice : {{ $rate | number_format(4, ',', '.') }} € </p>
<table class="table table-bordered table-hover">
    <tr class="active">
        <th>Date</th>
        <th>Motif de déplacement</th>
        <th>Distance</th>
        <th>Total</th>
    </tr>
    @foreach($declaration->trips as $trip)
        <tr>
            <td>{{ $trip->nicedate }}</td>
            <td>{{ $trip->content | slice(0,50) }}</td>
            <td>{{ $trip->distance }}</td>
            <td>
                {{ ($trip->distance * $rate) | number_format(2, ',', '.') }} €
            </td>
        </tr>
    @endforeach}
    <tr class="active">
        <td><strong>Sous Total</strong></td>
        <td></td>
        <td><strong>{{ $declarationSummary->totalKilometers }} Km</strong></td>
        <td>{{ $declarationSummary->totalMileageAllowance | number_format(2, ',', '.') }} €</td>
    </tr>
    <tr>
        <td><strong>Retenue Omnium :</strong>
            @if
                $declaration->omnium
                Oui
            @else
                Non
            @endif
        </td>
        <td>
            @if $declaration->omnium
                - {{ $declaration->rate_omnium }} €
            @endif
        </td>
        <td>
            @if $declaration->omnium
                {{ $declarationSummary->totalKilometers }}
            @endif
        </td>
        <td> {{ $declaration->retenueOmnium | number_format(2, ',', '.') }} €</td>
    </tr>
    <tr class="active">
        <td><strong>TOTAL A REMBOURSER</strong></td>
        <td colspan="2"></td>
        <td>{{ $declarationSummary->totalRefund | number_format(2, ',', '.') }} €</td>
    </tr>
</table>
