<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    @php
        $formattedDate = \Illuminate\Support\Str::title($summary['date']->translatedFormat('l j F Y'));
    @endphp
    <title>Export cuisine — {{ $formattedDate }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            padding: 16px;
        }

        h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 8px 0;
        }

        h3.title {
            color: #166534;
        }

        h4 {
            font-size: 14px;
            font-weight: 600;
            margin: 4px 0;
        }

        .menus-grid {
            display: table;
            width: 100%;
            border-spacing: 16px 0;
            margin-top: 12px;
        }

        .menus-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        table.menu-card {
            width: 100%;
            border-collapse: collapse;
        }

        table.menu-card th,
        table.menu-card td {
            border: 1px solid #999;
            padding: 6px 10px;
            font-size: 12px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h3 class="title">REPAS A DOMICILE : <strong>{{ $formattedDate }}</strong></h3>

    <h4><strong>Potages :</strong> {{ $summary['soup_total'] }}</h4>
    <h4><strong>Menus :</strong> {{ $summary['menus_total'] }}</h4>

    <div class="menus-grid">
        @foreach ($summary['menus'] as $menu)
            <div class="menus-cell">
                <table class="menu-card">
                    <tbody>
                        <tr>
                            <th>Menu {{ $menu['position'] }}</th>
                            <th>Nombre</th>
                        </tr>
                        <tr>
                            <th></th>
                            <td>{{ $menu['total'] }}</td>
                        </tr>
                        <tr>
                            <th colspan="2">Détails par régimes :</th>
                        </tr>
                        @forelse ($menu['diets'] as $diet)
                            <tr>
                                <td>{{ $diet['label'] }}</td>
                                <th>{{ $diet['total'] }}</th>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2"><em>Aucune commande.</em></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
</body>
</html>
