<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Déclaration de créance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #000;
            margin: 40px 60px;
            line-height: 1.6;
        }

        h1 {
            text-align: center;
            font-size: 20px;
            text-transform: uppercase;
            margin-bottom: 60px;
        }

        .amount {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 24px 0;
        }

        .signature {
            text-align: right;
            margin-right: 70px;
        }

        .spacer {
            height: 40px;
        }
    </style>
</head>
<body>
    <h1>Déclaration de créance</h1>

    Je soussigné(e), <strong>{{ mb_strtoupper($last_name) }} {{ $first_name }}</strong><br>
    domicilié(e) {{ $street }} à {{ $postal_code }} {{ $city }}

    <div class="spacer"></div>

    déclare qu'il est dû par la Ville de Marche-en-Famenne

    <p class="amount">la somme de {{ $amount }} €.</p>

    pour {!! nl2br(e($content)) !!}

    <div class="spacer"></div>

    À verser au compte n° {{ $iban }}

    <div class="spacer"></div>

    Certifié sincère et véritable à la somme de <strong>{{ $amount_in_words }} euros</strong>

    <div class="spacer"></div>
    <div class="spacer"></div>

    <p class="signature">Fait à Marche-en-Famenne, le {{ $filing_date }}</p>

    <div class="spacer"></div>
    <div class="spacer"></div>

    <p class="signature">Signature</p>
</body>
</html>
