<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') &middot; {{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 520px;
            margin: 64px auto;
            text-align: center;
        }
        h1 {
            font-size: 20px;
            margin-top: 0;
        }
        .btn {
            display: inline-block;
            margin-top: 16px;
            padding: 12px 24px;
            background-color: #dc2626;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="card">
        @yield('content')
    </div>
</body>
</html>
