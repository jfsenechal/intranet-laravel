<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brouillons de courrier à vérifier</title>
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
            background-color: #4f46e5;
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
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: white !important;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
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
        <h1>Brouillons de courrier à vérifier</h1>
    </div>

    <div class="content">
        @if($drafts->isEmpty())
            <p>Aucun courrier n'a pu être créé à partir de votre sélection.</p>
        @else
            <p>
                L'IA a créé <strong>{{ $drafts->count() }}</strong> brouillon(s) de courrier à partir des
                messages que vous avez sélectionnés dans la boîte mail.
            </p>

            <p>
                Ils ne sont visibles de personne d'autre et ne sont ni notifiés aux destinataires ni
                indexés tant que vous ne les avez pas validés. Vérifiez et complétez les informations
                proposées, puis validez : le brouillon suivant s'ouvre automatiquement.
            </p>

            <table>
                <tr>
                    <th>Numéro</th>
                    <th>Expéditeur</th>
                    <th>Description</th>
                </tr>
                @foreach($drafts as $draft)
                    <tr>
                        <td>{{ $draft->reference_number !== '' ? $draft->reference_number : '—' }}</td>
                        <td>{{ $draft->sender !== '' ? $draft->sender : '—' }}</td>
                        <td>{{ $draft->description ?: '—' }}</td>
                    </tr>
                @endforeach
            </table>

            <p>
                <a href="{{ $url }}" class="button">Vérifier les brouillons</a>
            </p>
        @endif

        @if($failures !== [])
            <div class="note">
                <p>
                    <strong>{{ count($failures) }} document(s) n'ont pas pu être analysés.</strong>
                    Les messages correspondants sont toujours dans la boîte mail et sont à encoder
                    manuellement :
                </p>
                <ul>
                    @foreach($failures as $failure)
                        <li>{{ $failure }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="footer">
            <p>
                Les informations ci-dessus ont été proposées par une intelligence artificielle et
                n'ont pas encore été relues.
            </p>
            <p>
                Vous retrouverez à tout moment les brouillons en attente dans
                <a href="{{ $draftsUrl }}">Brouillons IA</a>.
            </p>
        </div>
    </div>
</body>
</html>
