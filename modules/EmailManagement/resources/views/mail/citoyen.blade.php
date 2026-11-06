<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            border-bottom: 3px solid #0066cc;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            color: #0066cc;
            font-size: 18px;
            margin: 0;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Ville de Marche-en-Famenne</h1>
    </div>

    {!! $body !!}

    <div class="footer">
        <p>L'équipe informatique de la Ville de Marche-en-Famenne.</p>
        <p>Marche-en-Famenne. Rue Victor Libert,36 J. Tél: 084 32 70 54</p>
    </div>
</div>
</body>
</html>
