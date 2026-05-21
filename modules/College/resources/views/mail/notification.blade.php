<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: Arial, Helvetica, sans-serif; color: #1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="background-color: #1f2937; padding: 16px 24px; color: #ffffff; font-size: 16px; font-weight: bold;">
                            Collège communal
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px;">
                            <p style="margin: 0 0 16px; font-size: 14px; color: #6b7280;">
                                Collège du {{ $dateCollege->translatedFormat('d F Y') }}
                            </p>
                            <div style="font-size: 15px; line-height: 1.6;">
                                {!! $body !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 16px 24px; background-color: #f9fafb; font-size: 12px; color: #9ca3af;">
                            Ce message a été envoyé automatiquement par l'intranet communal.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
