<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $client->last_name }} {{ $client->first_name }} — commandes {{ $period }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            padding: 16px;
        }

        h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 12px 0;
            color: #166534;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 6px 10px;
            font-size: 12px;
            text-align: left;
        }

        tfoot th,
        tfoot td {
            font-weight: 700;
        }

        tfoot td.totals-cell,
        tfoot th.totals-cell {
            text-align: center;
        }
    </style>
</head>
<body>
    <h3>{{ $client->last_name }} {{ $client->first_name }} — commandes en <strong>{{ $period }}</strong></h3>

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
</body>
</html>
