<html lang="en">
<head>
    <title>Déclaration de déplacement</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .page-breaker {
            page-break-after: always;
        }
    </style>
</head>
<body class="bg-white text-gray-800 text-sm">

<div class="container mx-auto px-4 py-6">
    <table class="w-full border border-gray-300 border-collapse mb-6">
        <tr>
            <td class="border border-gray-300 p-3">
                Administration communale<br/>
                @php $logo = public_path('vendor/app/images/Marche_logo.png'); @endphp
                @inlinedImage($logo)
            </td>
            <td class="border border-gray-300 p-3 text-center align-middle">
                <h3 class="text-xl font-semibold">Frais de déplacements {{ $declaration->type_movement }}s</h3>
            </td>
        </tr>
    </table>

    <table class="w-full border border-gray-300 border-collapse mb-6">
        <tr>
            <td class="border border-gray-300 p-3">
                <strong>La Ville de Marche doit à :</strong><br/><br/>
            </td>
            <td class="border border-gray-300 p-3">
                {{ strtoupper($declaration->last_name) }} {{ $declaration->first_name }}<br/>
                {{ $declaration->street }}<br/>
                {{ $declaration->postal_code }} {{ $declaration->city }}
            </td>
        </tr>
        <tr>
            <th class="border border-gray-300 p-3 bg-gray-50 text-left">N° de compte IBAN</th>
            <td class="border border-gray-300 p-3">
                {{ $declaration->iban }}
            </td>
        </tr>
        <tr>
            <th class="border border-gray-300 p-3 bg-gray-50 text-left">Article budgétaire</th>
            <td class="border border-gray-300 p-3">
                {{ $declaration->budget_article }}
            </td>
        </tr>
    </table>

    <h4 class="text-green-600 font-semibold mb-4">Pour frais de déplacement :
        <strong>{{ number_format($declarationSummary->totalRefund, 2, ',', '.') }} €</strong></h4>
    <p class="mb-4">
        Le soussigné certifie que les déplacements "domicile-lieu de travail", effectués
        exceptionnellement et pour des raisons de service en dehors de son horaire normal,
        n'ont pas été compensés par une absence de déplacements lors de la récupération des heures supplémentaires
        prestées.
    </p>
    <p class="mb-6">
        Certifié sincère et véritable à la somme de
        <strong>{{ number_format($declarationSummary->totalRefund, 2, ',', '.') }} €</strong>
    </p>

    <table class="w-full border border-gray-300 border-collapse mb-6">
        <tr>
            <td class="border border-gray-300 p-3 w-1/2">
                Marche-en-Famenne, le {{ now()->format('d-m-Y') }}
            </td>
            <td class="border border-gray-300 p-3">
                Signature:
            </td>
        </tr>
    </table>

    <p class="mb-4">Délibération du Collège Communal du {{ $declaration->college_date?->format('d-m-Y') }}</p>
    <p class="mb-6">Certifié exact suivant le carnet de courses ci-annexé.</p>

    <p class="text-center font-bold mb-4">Le chef de service</p>
    <div class="page-breaker"></div>

    @if($declaration->type_movement === 'interne')
        @include('mileage::filament.export._internal_trips', ['rate' => $declaration->rate])
    @elseif($declaration->type_movement === 'externe')
        @include('mileage::filament.export._external_trips', ['rate' => $declaration->rate])
    @endif

    <div class="page-breaker"></div>
</div>
</body>
</html>
