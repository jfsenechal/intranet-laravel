<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des résidents</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            padding: 16px;
        }

        h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 4px 0;
            color: #166534;
        }

        .printed-at {
            font-size: 11px;
            color: #6b7280;
            margin: 0 0 12px 0;
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

        tfoot td {
            font-weight: 700;
        }
    </style>
</head>
<body>
    <h3>LISTE DES RÉSIDENTS</h3>

    <p class="printed-at">
        {{ $includeInactive ? 'Tous les résidents' : 'Résidents actifs uniquement' }}
        &nbsp;&mdash;&nbsp; imprimée le {{ $printedAt->format('d/m/Y') }}
    </p>

    @if ($residents->isEmpty())
        <p><em>Aucun résident encodé.</em></p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Chambre</th>
                    @if ($includeInactive)
                        <th>Actif</th>
                    @endif
                    <th>Remarques</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($residents as $resident)
                    <tr>
                        <td>{{ $resident->last_name }}</td>
                        <td>{{ $resident->first_name ?: '—' }}</td>
                        <td>{{ $resident->room ?: '—' }}</td>
                        @if ($includeInactive)
                            <td>{{ $resident->is_active ? 'oui' : 'non' }}</td>
                        @endif
                        <td>@if ($resident->notes){!! nl2br(e($resident->notes)) !!}@endif</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ $includeInactive ? 5 : 4 }}">
                        {{ $residents->count() }} {{ \Illuminate\Support\Str::plural('résident', $residents->count()) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif
</body>
</html>
