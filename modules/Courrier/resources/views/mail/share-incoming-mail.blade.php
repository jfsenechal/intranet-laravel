<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partage de courrier</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8fafc;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 600;
        }
        .note {
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 12px 16px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }
        a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Partage de courrier</h1>
    </div>

    <div class="content">
        <p>Un courrier vous a été partagé.</p>

        @if(filled($note))
            <div class="note">
                {!! nl2br(e($note)) !!}
            </div>
        @endif

        <table>
            <tr>
                <th>Numéro de référence</th>
                <td>{{ $incomingMail->reference_number }}</td>
            </tr>
            <tr>
                <th>Date du courrier</th>
                <td>{{ $incomingMail->mail_date?->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Expéditeur</th>
                <td>{{ $incomingMail->sender }}</td>
            </tr>
            @if(filled($incomingMail->description))
                <tr>
                    <th>Description</th>
                    <td>{!! $incomingMail->description !!}</td>
                </tr>
            @endif
        </table>

        @if($incomingMail->attachments->isNotEmpty())
            <p><strong>La ou les pièces jointes sont incluses dans cet email.</strong></p>
        @endif

        <div class="footer">
            <p>
                Consultez le courrier dans l'application :
                <a href="{{ $url }}">Voir le courrier</a>
            </p>
        </div>
    </div>
</body>
</html>
