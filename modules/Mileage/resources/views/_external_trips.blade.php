<h4 class="text-success">Détails des courses</h4>
<p class="text-warning">Tarif de l'exercice : {{ number_format($rate, 4, ',', '.') }} € </p>
<table class="table table-bordered table-hover">
    <tr class="active">
        <th>Date</th>
        <th>Voyage accompli</th>
        <th>Motif de déplacement</th>
        <th>Distance en Km</th>
        <th>Frais de repas</th>
        <th>Frais de train</th>
        <th style="width: 11%">Total</th>
    </tr>
    @foreach($declaration->trips as $trip)
    <tr>
        <td>
            {{ $trip->departure_date?->format('d-m-Y') }}
        </td>
        <td>
            De {{ $trip->departure_location }} à {{ $trip->arrival_location }}
        </td>
        <td>{{ Str::limit($trip->content, 50) }}</td>
        <td>{{ $trip->distance }}</td>
        <td>{{ number_format($trip->meal_expense ?? 0, 2, ',', '.') }} €</td>
        <td>{{ number_format($trip->train_expense ?? 0, 2, ',', '.') }} €</td>
        <td>
            {{ number_format(($trip->distance * $rate) + ($trip->meal_expense ?? 0) + ($trip->train_expense ?? 0), 2, ',', '.') }}
            €
        </td>
    </tr>
    @endforeach
    <tr class="active">
        <td><strong>Sous Total</strong></td>
        <td></td>
        <td></td>
        <td><strong>{{ $declarationSummary->totalKilometers }} Km</strong></td>
        <td><strong>{{ number_format($declarationSummary->mealExpense, 2, ',', '.') }} €</strong></td>
        <td><strong>{{ number_format($declarationSummary->trainExpense, 2, ',', '.') }} €</strong></td>
        <td><strong>{{ number_format($declarationSummary->totalMileageAllowance + $declarationSummary->totalExpense, 2, ',', '.') }} €</strong></td>
    </tr>
    <tr>
        <td><strong>Retenue Omnium :</strong>
            @if($declaration->omnium)
            Oui
            @else
            Non
            @endif
        </td>
        <td></td>
        <td></td>
        <td>
            @if($declaration->omnium)
            {{ $declarationSummary->totalKilometers }} Km
            @endif
        </td>
        @if($declaration->omnium)
        <td colspan="2">
            {{ $declaration->rate_omnium }} €
        </td>
        @else
        <td></td>
        <td></td>
        @endif
        <td>
            - {{ number_format($declarationSummary->totalOmnium, 2, ',', '.') }} €
        </td>
    </tr>
    <tr class="active">
        <td><strong>TOTAL A REMBOURSER</strong></td>
        <td colspan="5"></td>
        <td>{{ number_format($declarationSummary->totalRefund + $declarationSummary->totalExpense, 2, ',', '.') }} €</td>
    </tr>
</table>
