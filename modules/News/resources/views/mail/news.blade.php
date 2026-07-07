<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .email-logo {
            text-align: center;
            margin-bottom: 24px;
        }
        .email-logo img {
            max-width: 300px;
            height: auto;
        }
        .email-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 16px;
        }
        .email-body img {
            max-width: 100%;
            height: auto;
        }
        .alert-warning {
            margin-top: 24px;
            padding: 16px;
            border: 1px solid #f0c36d;
            border-radius: 6px;
            background-color: #fcf4dd;
            color: #7a5b00;
        }
        .alert-warning a {
            color: #2563eb;
            font-weight: 600;
        }
        .email-footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #e5e5e5;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        @if (! empty($logo))
            <div class="email-logo">
                <img src="{{ $message->embed($logo) }}" alt="Logo">
            </div>
        @endif

        <h1 class="email-title">{{ $news->name }}</h1>

        <div class="email-body">
            {!! $news->content !!}
        </div>

        @if (! $attachMedias && $mediasCount > 0)
            <div class="alert-warning">
                Attention, il y a {{ $mediasCount }} pièce(s) jointe(s) dans cette actualité, pour la (les) consulter,
                rendez-vous sur l'intranet.
                <br/><br/>
                <a href="{{ route('news.show', $news) }}">Consultez sur l'intranet</a>
            </div>
        @endif

        <div class="email-footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </div>
    </div>
</body>
</html>
