<x-filament-panels::page>
    <style>
        .monthly-orders table {
            width: 100%;
            border-collapse: collapse;
        }

        .monthly-orders th,
        .monthly-orders td {
            border: 1px solid #d4d4d8;
            padding: 0.5rem 0.75rem;
            text-align: left;
        }

        .monthly-orders tfoot th,
        .monthly-orders tfoot td {
            font-weight: 600;
        }

        .monthly-orders .totals-cell {
            text-align: center;
        }
    </style>

    <div class="monthly-orders">
        @if (count($summary['rows']) === 0)
            <p><em>Aucune commande pour cette période.</em></p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Jour</th>
                        <th>Potage</th>
                        <th>Menus 1</th>
                        <th>Menus 2</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summary['rows'] as $row)
                        <tr>
                            <td>{{ $row['date']->format('Y-m-d') }}</td>
                            <td>{{ $row['soup_count'] }}</td>
                            <td>{{ $row['menu_1'] }}</td>
                            <td>{{ $row['menu_2'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Totaux</th>
                        <td>{{ $summary['totals']['soup'] }}</td>
                        <td>{{ $summary['totals']['menu_1'] }}</td>
                        <td>{{ $summary['totals']['menu_2'] }}</td>
                    </tr>
                    <tr>
                        <th></th>
                        <th></th>
                        <th colspan="2" class="totals-cell">{{ $summary['totals']['menus'] }}</th>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</x-filament-panels::page>
