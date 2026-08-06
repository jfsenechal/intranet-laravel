<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>Notification de courriers entrants</title>
    <!--[if mso]>
    <style>
        body, table, td, div, p, a { font-family: Arial, sans-serif !important; }
    </style>
    <![endif]-->
    <style>
        /*
         * Progressive enhancement only: the Gmail app strips this block for
         * IMAP/Exchange accounts, so the layout below must hold up on its
         * inline styles alone. Here we only tighten spacing and fully drop the
         * secondary column, which otherwise wraps under the main one.
         */
        @media only screen and (max-width: 600px) {
            .wrapper {
                padding: 8px !important;
            }
            .header-cell {
                padding: 14px !important;
            }
            .header-title {
                font-size: 18px !important;
            }
            .content-cell {
                padding: 12px !important;
            }
            .card-cell {
                padding: 10px !important;
            }
            .col-main {
                max-width: 100% !important;
            }
            .desktop-only {
                display: none !important;
                max-height: 0 !important;
                overflow: hidden !important;
            }
        }
    </style>
</head>
<body style="margin:0; padding:0; width:100%; background-color:#eef2f7; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; font-family:Arial, sans-serif; color:#333333;">
    <div class="wrapper" style="padding:20px;">
        <!--[if mso]><table role="presentation" align="center" width="800" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
        <div style="max-width:800px; margin:0 auto;">

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                <tr>
                    <td class="header-cell" style="background-color:#2563eb; color:#ffffff; padding:20px; text-align:center; border-radius:8px 8px 0 0;">
                        <h1 class="header-title" style="margin:0; font-family:Arial, sans-serif; font-size:22px; line-height:1.3; color:#ffffff; font-weight:bold;">
                            Notification de courriers entrants
                        </h1>
                    </td>
                </tr>
                <tr>
                    <td class="content-cell" style="background-color:#f8fafc; padding:20px; border:1px solid #e2e8f0; border-top:none; border-radius:0 0 8px 8px;">

                        <p style="margin:0 0 12px 0; font-family:Arial, sans-serif; font-size:15px; line-height:1.6; color:#333333;">
                            Bonjour {{ $recipient->first_name }} {{ $recipient->last_name }},
                        </p>

                        <p style="margin:0 0 16px 0; font-family:Arial, sans-serif; font-size:15px; line-height:1.6; color:#333333;">
                            Vous avez recu {{ $incomingMails->count() }} {{ $incomingMails->count() > 1 ? 'nouveaux courriers' : 'nouveau courrier' }} :
                        </p>

                        @foreach($incomingMails as $courrier)
                        @php
                            $primaryServices = $courrier->services->where('pivot.is_primary', true)->pluck('name')->implode(', ');
                            $primaryRecipients = $courrier->recipients->where('pivot.is_primary', true)->map(fn ($r) => $r->first_name.' '.$r->last_name)->implode(', ');
                            $primaryTargets = collect([$primaryServices, $primaryRecipients])->filter()->implode(', ');

                            $secondaryServices = $courrier->services->where('pivot.is_primary', false)->pluck('name')->implode(', ');
                            $secondaryRecipients = $courrier->recipients->where('pivot.is_primary', false)->map(fn ($r) => $r->first_name.' '.$r->last_name)->implode(', ');
                            $secondaryTargets = collect([$secondaryServices, $secondaryRecipients])->filter()->implode(', ');
                        @endphp
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; background-color:#ffffff; border:1px solid #e2e8f0; border-radius:6px; margin:0 0 12px 0;">
                            <tr>
                                {{-- font-size:0 collapses the whitespace between the two inline-block columns. --}}
                                <td class="card-cell" style="padding:14px; font-size:0;">
                                    <!--[if mso]><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td width="470" valign="top"><![endif]-->
                                    <div class="col-main" style="display:inline-block; width:100%; max-width:470px; vertical-align:top;">
                                        <div style="font-family:Arial, sans-serif; font-size:16px; line-height:1.4; font-weight:bold; color:#0f172a;">
                                            {{ $courrier->sender }}
                                        </div>
                                        <div style="font-family:Arial, sans-serif; font-size:15px; line-height:1.5; padding-top:6px;">
                                            <a href="{{ route('filament.courrier-panel.resources.incoming-mails.view', ['record' => $courrier->id]) }}" style="color:#2563eb; text-decoration:underline;">{{ $courrier->description }}</a>
                                        </div>
                                        <div style="font-family:Arial, sans-serif; font-size:12px; line-height:1.5; color:#94a3b8; padding-top:6px;">
                                            Numero {{ $courrier->reference_number }}
                                        </div>
                                    </div>
                                    <!--[if mso]></td><td width="240" valign="top"><![endif]-->
                                    <div class="desktop-only" style="display:inline-block; width:100%; max-width:240px; vertical-align:top;">
                                        <div style="font-family:Arial, sans-serif; font-size:13px; line-height:1.5; color:#475569;">
                                            <span style="color:#64748b;">Original a :</span> {{ $primaryTargets !== '' ? $primaryTargets : '-' }}
                                        </div>
                                        <div style="font-family:Arial, sans-serif; font-size:13px; line-height:1.5; color:#475569; padding-top:4px;">
                                            <span style="color:#64748b;">Copie a :</span> {{ $secondaryTargets !== '' ? $secondaryTargets : '-' }}
                                        </div>
                                        @if($courrier->is_registered || $courrier->has_acknowledgment)
                                        <div style="padding-top:6px;">
                                            @if($courrier->is_registered)
                                                <span style="display:inline-block; padding:2px 8px; border-radius:4px; font-family:Arial, sans-serif; font-size:12px; font-weight:500; background-color:#fef3c7; color:#92400e;">Recommande</span>
                                            @endif
                                            @if($courrier->has_acknowledgment)
                                                <span style="display:inline-block; padding:2px 8px; border-radius:4px; font-family:Arial, sans-serif; font-size:12px; font-weight:500; background-color:#dbeafe; color:#1e40af;">Accuse</span>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                    <!--[if mso]></td></tr></table><![endif]-->
                                </td>
                            </tr>
                        </table>
                        @endforeach

                        @if($attachmentsOmitted)
                        <p style="margin:16px 0 0 0; background-color:#fef3c7; border:1px solid #fcd34d; color:#92400e; padding:12px; border-radius:4px; font-family:Arial, sans-serif; font-size:14px; line-height:1.6;">
                            <strong>Attention :</strong> les pieces jointes
                            ({{ $attachmentsCount }} {{ $attachmentsCount > 1 ? 'fichiers' : 'fichier' }},
                            {{ \Illuminate\Support\Number::fileSize($attachmentsSize, precision: 1) }})
                            n'ont pas pu etre jointes a cet email : leur taille totale depasse la limite
                            autorisee par le serveur de messagerie. Vous pouvez les consulter et les
                            telecharger dans l'application.
                        </p>
                        @elseif($attachmentsUnavailable)
                        <p style="margin:16px 0 0 0; background-color:#fef3c7; border:1px solid #fcd34d; color:#92400e; padding:12px; border-radius:4px; font-family:Arial, sans-serif; font-size:14px; line-height:1.6;">
                            <strong>Attention :</strong> les pieces jointes n'ont pas pu etre jointes a cet
                            email. Vous pouvez les consulter et les telecharger dans l'application.
                        </p>
                        @elseif($attachmentsCount > 0)
                        <p style="margin:16px 0 0 0; font-family:Arial, sans-serif; font-size:14px; line-height:1.6; color:#333333;">
                            <strong>Note:</strong> Les pieces jointes sont incluses dans cet email.
                        </p>
                        @endif

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; margin-top:20px; border-top:1px solid #e2e8f0;">
                            <tr>
                                <td style="padding-top:16px; font-family:Arial, sans-serif; font-size:14px; line-height:1.6; color:#64748b;">
                                    <p style="margin:0 0 8px 0;">
                                        Consultez l'application pour plus de details :
                                        <a href="{{ $url }}" style="color:#2563eb; text-decoration:underline;">Acceder a l'indicateur</a>
                                    </p>
                                    <p style="margin:0;">Cet email a ete envoye automatiquement. Merci de ne pas y repondre.</p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>

        </div>
        <!--[if mso]></td></tr></table><![endif]-->
    </div>
</body>
</html>
