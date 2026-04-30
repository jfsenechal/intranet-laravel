<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification de courriers entrants</title>
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
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-recommande {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-accuse {
            background-color: #dbeafe;
            color: #1e40af;
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
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Notification de courriers entrants</h1>
    </div>

    <div class="content">
        <p>Bonjour {{ $recipient->first_name }} {{ $recipient->last_name }},</p>

        <p>Vous avez recu {{ $incomingMails->count() }} {{ $incomingMails->count() > 1 ? 'nouveaux courriers' : 'nouveau courrier' }} :</p>

        <table>
            <thead>
                <tr>
                    <th>Numero</th>
                    <th>Expediteur</th>
                    <th>Description</th>
                    <th>Original a</th>
                    <th>Copie a</th>
                    <th>Recomm / Accuse</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incomingMails as $courrier)
                <tr>
                    <td>{{ $courrier->reference_number }}</td>
                    <td>{{ $courrier->sender }}</td>
                    <td>{{ $courrier->description }}</td>
                    <td>
                        @php
                            $primaryServices = $courrier->services->where('pivot.is_primary', true)->pluck('name')->implode(', ');
                            $primaryRecipients = $courrier->recipients->where('pivot.is_primary', true)->map(fn($r) => $r->first_name . ' ' . $r->last_name)->implode(', ');
                        @endphp
                        {{ collect([$primaryServices, $primaryRecipients])->filter()->implode(', ') }}
                    </td>
                    <td>
                        @php
                            $secondaryServices = $courrier->services->where('pivot.is_primary', false)->pluck('name')->implode(', ');
                            $secondaryRecipients = $courrier->recipients->where('pivot.is_primary', false)->map(fn($r) => $r->first_name . ' ' . $r->last_name)->implode(', ');
                        @endphp
                        {{ collect([$secondaryServices, $secondaryRecipients])->filter()->implode(', ') }}
                    </td>
                    <td>
                        @if($courrier->is_registered)
                            <span class="badge badge-recommande">Recommande</span>
                        @endif
                        @if($courrier->has_acknowledgment)
                            <span class="badge badge-accuse">Accuse</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($recipient->receives_attachments && $incomingMails->flatMap->attachments->isNotEmpty())
        <p><strong>Note:</strong> Les pieces jointes sont incluses dans cet email.</p>
        @endif

        <div class="footer">
            <p>
                Consultez l'application pour plus de details :
                <a href="{{ $url }}">Acceder a l'indicateur</a>
            </p>
            <p>Cet email a ete envoye automatiquement. Merci de ne pas y repondre.</p>
        </div>
    </div>
</body>
</html>
