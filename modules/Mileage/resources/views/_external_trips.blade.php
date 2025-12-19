<h4 class="text-success">Détails des courses</h4>
<p class="text-warning">Tarif de l'exercice : {{ $rate | number_format(4, ',', '.') }} € </p>
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
            {{ $declaration->nicedate }}
        </td>
        <td>
            De {{ $trip->departure_location }} à {{ $trip->arrival_location }}
        </td>
        <td>{{ $trip->content | slice(0,50) }}</td>
        <td>{{ $trip->distance }}</td>
        <td>{{ $trip->meal_expense | number_format(2, ',', '.') }} €</td>
        <td>{{ $trip->train_expense | number_format(2, ',', '.') }} €</td>
        <td>
            {{ (($trip->distance * $rate) + $trip->meal_expense + $trip->train_expense ) | number_format(2, ',', '.') }}
            €
        </td>
    </tr>
    @endforeach}
    <tr class="active">
        <td><strong>Sous Total</strong></td>
        <td></td>
        <td></td>
        <td><strong>{{ $declarationSummary->totalKilometers }} Km</strong></td>
        <td><strong>{{ $declarationSummary->repasTotal | number_format(2, ',', '.') }} €</strong></td>
        <td><strong>{{ $declarationSummary->trainTotal | number_format(2, ',', '.') }} €</strong></td>
        <td><strong>{{ $declarationSummary->remboursementTotal | number_format(2, ',', '.') }} €</strong></td>
    </tr>
    <tr>
        <td><strong>Retenue Omnium :</strong>
            {% if declaration.omnium %}
            Oui
            {% else %}
            Non
            {% endif %}
        </td>
        <td></td>
        <td></td>
        <td>
            {% if declaration.omnium %}
            {{ $declaration->distancetotale }} Km
            {% endif %}
        </td>
        {% if declaration.omnium %}
        <td colspan="2">
            {{ $declaration->tarifomnium }} €
        </td>
        {% else %}
        <td></td>
        <td></td>
        {% endif %}
        <td>
            - {{ $declaration->retenueOmnium | number_format(2, ',', '.') }} €
        </td>
    </tr>
    <tr class="active">
        <td><strong>TOTAL A REMBOURSER</strong></td>
        <td colspan="5"></td>
        <td>{{ $declaration->totalRefund | number_format(2, ',', '.') }} €</td>
    </tr>
</table>
