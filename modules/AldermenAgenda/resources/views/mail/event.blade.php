<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->title }}</title>
</head>
<body style="background-color: #f8fafc; font-family: Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; margin: 0; padding: 0;">
<div style="max-width: 752px; margin: 0 auto; padding: 24px;">
    @if($isPreview)
        <p style="background-color: #fef3c7; color: #92400e; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin: 0 0 16px;">
            Ceci est un aperçu de l'événement. Cet e-mail n'a pas été envoyé aux destinataires.
        </p>
    @endif

    <table style="width: 100%; background-color: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0;" cellpadding="0" cellspacing="0" role="none">
        <tr>
            <td style="padding: 24px 36px;">
                @if(! empty($logo))
                    <a href="{{ config('app.url') }}">
                        <img src="{{ $message->embed($logo) }}" width="70" alt="logo">
                    </a>
                @endif

                <h2 style="color: #1e293b; margin-top: 16px;">{{ $event->name }}</h2>

                @if($event->event_type)
                    <p style="color: #64748b; font-size: 13px; margin: 0 0 16px;">
                        {{ $event->event_type->getLabel() }}
                    </p>
                @endif

                @if($event->description)
                    <div style="color: #475569; font-size: 15px; line-height: 1.6; margin-bottom: 16px;">
                        {{ $event->description }}
                    </div>
                @endif

                <table style="width: 100%; color: #475569; font-size: 14px; line-height: 1.6;" cellpadding="0" cellspacing="0" role="none">
                    @if($event->organizer)
                        <tr>
                            <td style="padding: 4px 12px 4px 0; color: #94a3b8;">Organisateur</td>
                            <td style="padding: 4px 0;">{{ $event->organizer->getLabel() }}</td>
                        </tr>
                    @endif
                    @if($event->start_at)
                        <tr>
                            <td style="padding: 4px 12px 4px 0; color: #94a3b8;">Date de début</td>
                            <td style="padding: 4px 0;">{{ $event->start_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endif
                    @if($event->end_at)
                        <tr>
                            <td style="padding: 4px 12px 4px 0; color: #94a3b8;">Date de fin</td>
                            <td style="padding: 4px 0;">{{ $event->end_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endif
                    @if($event->location)
                        <tr>
                            <td style="padding: 4px 12px 4px 0; color: #94a3b8;">Lieu</td>
                            <td style="padding: 4px 0;">{{ $event->location }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <p style="color: #94a3b8; font-size: 12px; text-align: center; margin-top: 16px;">
        {{ config('app.name') }}
    </p>
</div>
</body>
</html>
