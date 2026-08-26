<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $heading }}</title>
    <style>
        html { -webkit-print-color-adjust: exact; }

        @page { margin: 14mm 10mm; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            font-size: 12px;
        }

        h3 {
            font-size: 16px;
            font-weight: 600;
            margin: 6px 0 12px;
            color: #166534;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #00AA88;
            padding: 6px 10px;
        }

        th { background: #f5f5f5; text-align: center; }

        td.number { text-align: right; }

        tfoot td { font-weight: 600; background: #f5f5f5; }
    </style>
</head>
<body>
    <h3>{{ $heading }}</h3>

    <table>
        <thead>
            <tr>
                <th style="width:40%;">Date</th>
                <th style="width:15%;">Clients</th>
                <th style="width:15%;">Potages</th>
                <th style="width:15%;">Menus 1</th>
                <th style="width:15%;">Menus 2</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($days as $day)
                <tr>
                    <td>{{ $day['label'] }}</td>
                    <td class="number">{{ $day['clients_count'] }}</td>
                    <td class="number">{{ $day['soup_count'] }}</td>
                    <td class="number">{{ $day['menu1_count'] }}</td>
                    <td class="number">{{ $day['menu2_count'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Aucun jour de repas.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($days !== [])
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td class="number">{{ $totals['clients_count'] }}</td>
                    <td class="number">{{ $totals['soup_count'] }}</td>
                    <td class="number">{{ $totals['menu1_count'] }}</td>
                    <td class="number">{{ $totals['menu2_count'] }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
