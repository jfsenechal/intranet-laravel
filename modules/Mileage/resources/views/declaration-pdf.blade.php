<html lang="en">
<head>
    <title>Action</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

<div class="container">
    <table class="table table-bordered table-hover">
        <tr>
            <td>
                Administration communale<br/>
                @php $logo = public_path('vendor/app/images/Marche_logo.png'); @endphp
                @inlinedImage($logo)
            </td>
            <td class="text-center" style="vertical-align: middle"><h3> Frais de déplacements {{ $declaration->type_movement }}s</h3></td>
        </tr>
    </table>

    <table class="table table-bordered table-hover">
        <tr>
            <td>
                <strong>La Ville de Marche doit à :</strong><br/><br/>
            </td>
            <td>
                {{ strtoupper($declaration->last_name) }} {{ $declaration->first_name }}<br/>
                {{ $declaration->street }}<br/>
                {{ $declaration->postal_code }} {{ $declaration->city }}
            </td>
        </tr>
        <tr>
            <th>N° de compte IBAN</th>
            <td>
                {{ $declaration->iban }}
            </td>
        </tr>
        <tr>
            <th>Article budgétaire</th>
            <td>
                {{ $declaration->budget_article }}
            </td>
        </tr>
    </table>

    <h4 class="text-success">Pour frais de déplacement :
        <strong>{{ number_format($declarationSummary->totalRefund, 2, ',', '.') }} €</strong></h4>
    Le soussigné certifie que les déplacements "domicilie-lieu de travail", effectués
    exceptionnellement et <br/> pour des raisons de service en dehors de son horaire normal,
    n'ont pas été compensés par une absence de déplacements lors de la récupération des heures supplémentaires prestées.
    <br/><br/>
    Certifié sincère et véritable à la somme de <strong>{{ number_format($declarationSummary->totalRefund, 2, ',', '.') }}
        €</strong>
    <br/><br/>

    <table class="table table-bordered table-hover">
        <tr>
            <td width="50%">
                Marche-en-Famenne, le {{ now()->format('d-m-Y') }}
            </td>
            <td>
                Signature:
            </td>
    </table>

    Délibération du Collège Communal du {{ $declaration->college_date?->format('d-m-Y') }}
    <br/><br/>
    Certifié exact suivant le carnet de courses ci-annexé.
    <br/><br/>

    <p class="text-center"><strong>Le chef de service</strong></p>
    <div class="page-breaker"></div>

    @if($declaration->type_movement === 'interne')
        @include('mileage::_internal_trips', ['rate' => $declaration->rate])
    @elseif($declaration->type_movement === 'externe')
        @include('mileage::_external_trips', ['rate' => $declaration->rate])
    @endif

    <div class="page-breaker"></div>
</div>
</body>
</html>
