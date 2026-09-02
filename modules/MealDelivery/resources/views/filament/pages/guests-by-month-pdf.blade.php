<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Invités par mois — {{ $period }}</title>
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
            vertical-align: top;
        }

        tfoot th,
        tfoot td {
            font-weight: 700;
        }
    </style>
</head>
<body>
    <h3>Repas invités par mois <strong>{{ $period }}</strong></h3>

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
                        <td>{{ $client->last_name }} {{ $client->first_name }}</td>
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
</body>
</html>
