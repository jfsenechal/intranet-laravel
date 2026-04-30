<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Télétravail' }}</title>
</head>
<body style="background-color: #f8fafc; font-family: Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; margin: 0; padding: 0;">
<div style="max-width: 752px; margin: 0 auto; padding: 24px;">
    <table style="width: 100%; background-color: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0;" cellpadding="0" cellspacing="0" role="none">
        <tr>
            <td style="padding: 24px 36px;">
                @if(! empty($logo))
                    <a href="{{ config('app.url') }}">
                        <img src="{{ $message->embed($logo) }}" width="70" alt="logo">
                    </a>
                @endif
                <h2 style="color: #1e293b; margin-top: 16px;">{{ $title ?? 'Télétravail' }}</h2>
                <div style="color: #475569; font-size: 15px; line-height: 1.6;">
                    {{ $slot }}
                </div>
            </td>
        </tr>
    </table>
    <p style="color: #94a3b8; font-size: 12px; text-align: center; margin-top: 16px;">
        {{ config('app.name') }}
    </p>
</div>
</body>
</html>
