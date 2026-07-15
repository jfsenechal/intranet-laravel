<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de pièce jointe</title>
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
            background-color: #d97706;
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
            background-color: #fffbeb;
            border-left: 4px solid #d97706;
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
        <h1>Demande de pièce jointe</h1>
    </div>

    <div class="content">
        <p>
            <strong>{{ $askerName }}</strong> (<a href="mailto:{{ $askerEmail }}">{{ $askerEmail }}</a>)
            souhaite recevoir la pièce jointe du courrier ci-dessous.
        </p>

        <p>
            Vous pouvez la lui transmettre en ouvrant le courrier et en utilisant le
            bouton <em>Partager le courrier</em>.
        </p>

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

        <div class="footer">
            <p>
                Consultez le courrier dans l'application :
                <a href="{{ $url }}">Voir le courrier</a>
            </p>
        </div>
    </div>
</body>
</html>
