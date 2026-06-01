<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $classifiedAd->name }}</title>
</head>
<body style="background-color: #f8fafc; font-family: Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; margin: 0; padding: 0;">
<div style="max-width: 752px; margin: 0 auto; padding: 24px;">
    <table style="width: 100%; background-color: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0;" cellpadding="0" cellspacing="0" role="none">
        <tr>
            <td style="padding: 24px 36px;">
                @if(! empty($logo))
                    <a href="{{ $url }}">
                        <img src="{{ $message->embed($logo) }}" width="70" alt="logo">
                    </a>
                @endif

                <p style="color: #be185d; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; margin: 16px 0 4px;">
                    Nouvelle petite annonce
                </p>

                <h2 style="color: #1e293b; margin: 0 0 16px;">{{ $classifiedAd->name }}</h2>

                @if($classifiedAd->category)
                    <p style="color: #64748b; font-size: 13px; margin: 0 0 16px;">
                        {{ $classifiedAd->category->name }}
                    </p>
                @endif

                @if($classifiedAd->excerpt)
                    <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 16px;">
                        {{ $classifiedAd->excerpt }}
                    </p>
                @endif

                <div style="color: #475569; font-size: 15px; line-height: 1.6; margin-bottom: 16px;">
                    {!! $classifiedAd->content !!}
                </div>

                <table style="width: 100%; color: #475569; font-size: 14px; line-height: 1.6;" cellpadding="0" cellspacing="0" role="none">
                    @if($classifiedAd->end_date)
                        <tr>
                            <td style="padding: 4px 12px 4px 0; color: #94a3b8;">Date de fin</td>
                            <td style="padding: 4px 0;">{{ $classifiedAd->end_date }}</td>
                        </tr>
                    @endif
                    @if($classifiedAd->user_add)
                        <tr>
                            <td style="padding: 4px 12px 4px 0; color: #94a3b8;">Auteur</td>
                            <td style="padding: 4px 0;">{{ $classifiedAd->user_add }}</td>
                        </tr>
                    @endif
                </table>

                <p style="margin-top: 24px;">
                    <a href="{{ $url }}" style="display: inline-block; background-color: #be185d; color: #ffffff; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        Voir sur l'intranet
                    </a>
                </p>
            </td>
        </tr>
    </table>

    <p style="color: #94a3b8; font-size: 12px; text-align: center; margin-top: 16px;">
        {{ config('app.name') }}
    </p>
</div>
</body>
</html>
