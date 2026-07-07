<!DOCTYPE html>
<html lang="fr" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Intranet — Accueil</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .animate-fade-in-up { animation: fade-in-up 0.6s ease-out both; }
        .animate-float { animation: float 3s ease-in-out infinite; }

        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -10px rgb(0 0 0 / 0.15);
        }

        /*
         * Semantic wayfinding palette — colour encodes the *kind* of content,
         * applied consistently so staff learn the mapping. Four families:
         *   Info / communications  → blue–indigo
         *   Resources              → teal
         *   People / HR            → warm orange
         *   Community / social     → rose
         * Each header uses a single-hue gradient (light → dark of the same hue)
         * for depth without a multi-colour "marketing" sweep. All start stops are
         * dark enough for white header text (WCAG AA, large text ≥ 3:1).
         */

        /* Info / communications */
        .gradient-hero { background: linear-gradient(135deg, #1e40af 0%, #312e81 100%); }
        .gradient-news { background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%); }
        .gradient-press { background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%); }
        .gradient-rss { background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%); }

        /* Resources */
        .gradient-documents { background: linear-gradient(135deg, #0f766e 0%, #134e4a 100%); }

        /* People / HR */
        .gradient-birthday { background: linear-gradient(135deg, #c2410c 0%, #7c2d12 100%); }
        .gradient-employee { background: linear-gradient(135deg, #c2410c 0%, #7c2d12 100%); }

        /* Community / social */
        .gradient-classified { background: linear-gradient(135deg, #be123c 0%, #881337 100%); }
        .gradient-sport { background: linear-gradient(135deg, #be123c 0%, #881337 100%); }

        [style*="--delay"] { animation-delay: var(--delay); }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="isolate min-h-full bg-linear-to-br from-gray-50 to-gray-100 text-gray-900">

    <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/80 backdrop-blur-md">
        <div class="mx-auto flex max-w-screen-2xl items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-linear-to-br from-blue-600 to-indigo-700 font-bold text-white shadow-md">
                    M
                </div>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-gray-500">Intranet</p>
                    <p class="text-lg font-bold text-gray-900">Ville/Cpas de Marche-en-Famenne</p>
                </div>
            </div>
            @auth
                <div class="flex items-center gap-3">
                    <livewire:modules-launcher />
                    <a
                        href="{{ url('/my-space') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-blue-700 hover:shadow-lg"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {{ auth()->user()->full_name }}
                    </a>
                </div>
            @else
                <a
                    href="{{ route('filament.app-panel.auth.login') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-blue-700 hover:shadow-lg"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Se connecter
                  </a>
            @endauth
        </div>
    </header>

    <main class="mx-auto w-full max-w-screen-2xl space-y-8 px-6 py-8">
        <livewire:home.hero-banner />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <livewire:home.latest-news />

            <div class="space-y-6">
                <livewire:home.birthdays />
                <livewire:home.favorites />
                {{-- <livewire:home.classified-ads /> --}}
                <livewire:home.latest-employees />
                <livewire:home.sport-activities />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <livewire:home.latest-documents />
            <livewire:home.press-articles />
        </div>

        <livewire:home.rss-feeds />
    </main>

    <footer class="mt-12 border-t border-gray-200 bg-white">
        <div class="mx-auto max-w-screen-2xl px-6 py-6 text-center text-sm text-gray-500">
            © {{ date('Y') }} Ville de Marche-en-Famenne — Intranet
        </div>
    </footer>

    @livewireScripts
</body>
</html>
