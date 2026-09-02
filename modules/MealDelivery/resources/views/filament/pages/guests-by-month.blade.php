<x-filament-panels::page>
    @php
        $summary = $this->getSummary();
    @endphp

    <style>
        .guests-by-month table {
            width: 100%;
            border-collapse: collapse;
        }

        .guests-by-month th,
        .guests-by-month td {
            border: 1px solid #d4d4d8;
            padding: 0.5rem 0.75rem;
            text-align: left;
            vertical-align: top;
        }

        .guests-by-month a {
            color: #be185d;
            text-decoration: underline;
        }

        .guests-by-month tfoot th,
        .guests-by-month tfoot td {
            font-weight: 600;
        }
    </style>

    <div class="guests-by-month">
        @if (count($summary['rows']) === 0)
            <p><em>Aucun repas invité pour cette période.</em></p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Adresse</th>
                        <th>Menu 1</th>
                        <th>Menu 2</th>
                        <th>Total repas invités</th>
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
                            <td>{{ trim($client->street.' '.$client->number) }}</td>
                            <td>{{ $row['menu1_total'] }}</td>
                            <td>{{ $row['menu2_total'] }}</td>
                            <td>{{ $row['guests_total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">Totaux</th>
                        <td>{{ $summary['totals']['menu1'] }}</td>
                        <td>{{ $summary['totals']['menu2'] }}</td>
                        <td>{{ $summary['totals']['guests'] }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</x-filament-panels::page>
