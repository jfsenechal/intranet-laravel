<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .page-breaker {
            page-break-after: always;
        }
    </style>
    <title>Déclarations annuelles</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .text-center { text-align: center; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f5f5f5; }
        .table-striped tbody tr:nth-child(odd) { background-color: #fafafa; }
    </style>
</head>
<body>
<div class="container">

    <h3 class="text-center">Récapitulatif des frais de déplacement {{ $year }}</h3>

    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Employé</th>
            <th>N° d'immatriculation</th>
            <th>Omnium</th>
            <th>Distance en Km</th>
        </tr>
        </thead>
        <tbody>
        @foreach($declarations as $declaration)
        <tr>
            <td>{{ strtoupper($declaration['last_name']) }} {{ $declaration['first_name'] }}</td>
            <td>
                {{ strtoupper($declaration['car_license_plate1'] ?? '') }}
                @if($declaration['car_license_plate2'])
                    ou {{ strtoupper($declaration['car_license_plate2']) }}
                @endif
            </td>
            <td>
                @if($declaration['omnium'])
                    &#10003;
                @endif
            </td>
            <td>{{ number_format($declaration['distance'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr>
            <td><strong>Distance totale (Km)</strong></td>
            <td></td>
            <td></td>
            <td><strong>{{ number_format($totalKilometers, 0, ',', '.') }} Km</strong></td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
