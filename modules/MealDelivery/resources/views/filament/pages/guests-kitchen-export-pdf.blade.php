<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Repas invités — {{ $formattedDate }}</title>
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
    <h3>REPAS INVITÉS : <strong>{{ $formattedDate }}</strong></h3>

    @if (count($summary['rows']) === 0)
        <p><em>Aucun repas invité pour ce jour.</em></p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Résident</th>
                    <th>Menu 1</th>
                    <th>Menu 2</th>
                    <th>Total</th>
                    <th>Remarques</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($summary['rows'] as $row)
                    <tr>
                        <td>{{ $row['resident_name'] }}</td>
                        <td>{{ $row['menu1'] > 0 ? $row['menu1'] : '' }}</td>
                        <td>{{ $row['menu2'] > 0 ? $row['menu2'] : '' }}</td>
                        <td>{{ $row['total'] }}</td>
                        <td>@if ($row['notes']){!! nl2br(e($row['notes'])) !!}@endif</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>Totaux</th>
                    <td>{{ $summary['totals']['menu1'] }}</td>
                    <td>{{ $summary['totals']['menu2'] }}</td>
                    <td>{{ $summary['totals']['guests'] }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif
</body>
</html>
