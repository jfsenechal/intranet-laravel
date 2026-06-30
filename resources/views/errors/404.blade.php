<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Page introuvable — Intranet Marche-en-Famenne</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet"/>
    <style>
        :root {
            --ink: #111827;
            --muted: #6b7280;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { height: 100%; }

        body {
            display: flex;
            min-height: 100%;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, sans-serif;
            color: var(--ink);
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            -webkit-font-smoothing: antialiased;
        }

        .card {
            width: 100%;
            max-width: 32rem;
            padding: 2.5rem;
            text-align: center;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            box-shadow: 0 20px 40px -12px rgb(0 0 0 / 0.12);
            animation: fade-in-up 0.6s ease-out both;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3.5rem;
            height: 3.5rem;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, #1e40af 0%, #312e81 100%);
            border-radius: 0.875rem;
            box-shadow: 0 10px 25px -8px rgb(49 46 129 / 0.55);
        }

        .code {
            font-size: 3.75rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #1e40af 0%, #312e81 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h1 {
            margin-top: 1rem;
            font-size: 1.375rem;
            font-weight: 700;
        }

        p {
            margin-top: 0.75rem;
            font-size: 0.975rem;
            line-height: 1.6;
            color: var(--muted);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 0.625rem;
            transition: background 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .btn:hover { transform: translateY(-1px); }

        .btn-primary {
            color: #fff;
            background: #2563eb;
            box-shadow: 0 8px 20px -8px rgb(37 99 235 / 0.6);
        }

        .btn-primary:hover { background: #1d4ed8; }

        .btn-secondary {
            color: #374151;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
        }

        .btn-secondary:hover { background: #e5e7eb; }

        .footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f3f4f6;
            font-size: 0.8rem;
            color: #9ca3af;
        }

        @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @media (prefers-reduced-motion: reduce) {
            .card { animation: none; }
            .btn:hover { transform: none; }
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="logo">M</div>

        <div class="code">404</div>
        <h1>Page introuvable</h1>
        <p>
            La page que vous recherchez n'existe pas, a été déplacée ou n'est plus disponible.
            Vérifiez l'adresse saisie ou revenez à la page d'accueil.
        </p>

        <div class="actions">
            <a href="{{ url('/') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10" />
                </svg>
                Retour à l'accueil
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Page précédente
            </a>
        </div>

        <div class="footer">
            Intranet — Ville/Cpas de Marche-en-Famenne
        </div>
    </main>
</body>
</html>
