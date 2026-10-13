<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .email-title {
            margin: 0;
            font-size: 22px;
            line-height: 1.3;
            color: #222;
        }
        .email-body {
            margin-top: 16px;
        }
        .email-body img {
            max-width: 100%;
            height: auto;
        }
        .email-header {
            width: 100%;
            border-collapse: collapse;
        }
        .email-header td {
            padding: 0;
            vertical-align: middle;
        }
        .email-logo {
            width: 96px;
            padding-right: 16px !important;
        }
        .email-logo img {
            display: block;
            width: 96px;
            max-width: 96px;
            height: auto;
        }
        .email-footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #e5e5e5;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
        .email-unsubscribe {
            margin-top: 12px;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
        .email-unsubscribe a {
            color: #999;
        }
    </style>
</head>
<body>
    <div class="email-container">
        @if(!empty($logoUrl))
            <table class="email-header" role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td class="email-logo" width="96" style="width: 96px; padding-right: 16px; vertical-align: middle;">
                        <img src="{{ $logoUrl }}" alt="Logo" width="96" style="display: block; width: 96px; max-width: 96px; height: auto;">
                    </td>
                    @if(!empty($subject))
                        <td style="vertical-align: middle;">
                            <h1 class="email-title">{{ $subject }}</h1>
                        </td>
                    @endif
                </tr>
            </table>
        @elseif(!empty($subject))
            <h1 class="email-title">{{ $subject }}</h1>
        @endif
        <div class="email-body">
            {!! $body !!}
        </div>
        @if(!empty($signature))
            <div class="email-signature">
                {!! $signature !!}
            </div>
        @endif
        <div class="email-footer">
            @if(!empty($footer))
                {!! $footer !!}
            @else
                &copy; {{ date('Y') }} {{ config('app.name') }}
            @endif
        </div>
        @if(!empty($unsubscribeUrl))
            <div class="email-unsubscribe">
                <a href="{{ $unsubscribeUrl }}">Se désabonner</a>
            </div>
        @endif
    </div>
</body>
</html>
