<x-filament-panels::page>
    @php
        $summary = $this->getSummary();
        $clientResourceUrl = \AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource::getUrl();
    @endphp

    <style>
        .orders-by-month table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-by-month th,
        .orders-by-month td {
            border: 1px solid #d4d4d8;
            padding: 0.5rem 0.75rem;
            text-align: left;
            vertical-align: top;
        }

        .orders-by-month a {
            color: #be185d;
            text-decoration: underline;
        }

        .orders-by-month tfoot th,
        .orders-by-month tfoot td {
            font-weight: 600;
        }
    </style>

    <div class="orders-by-month">
        @if (count($summary['rows']) === 0)
            <p><em>Aucune commande pour cette période.</em></p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Adresse</th>
                        <th>Total potage</th>
                        <th>Total repas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summary['rows'] as $row)
                        @php($client = $row['client'])
                        <tr>
                            <td>
                                <a href="{{ \AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource::getUrl('view', ['record' => $client->id]) }}">
                                    {{ $client->last_name }} {{ $client->first_name }}
                                </a>
                            </td>
                            <td>
                                {{ trim($client->street.' '.$client->number) }}<br>
                                {{ $client->postal_code }} {{ $client->city }}
                            </td>
                            <td>{{ $row['soup_total'] }}</td>
                            <td>{{ $row['menus_total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">Totaux</th>
                        <td>{{ $summary['totals']['soup'] }}</td>
                        <td>{{ $summary['totals']['menus'] }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</x-filament-panels::page>
