<!DOCTYPE html>
<html lang="fr" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $news->name }} — Actualités</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/css/filament-public.css'])
    @livewireStyles
    @filamentStyles
    <style>
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        /* Same blue as the "Info / communications" family of the homepage palette. */
        .gradient-news { background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%); }
    </style>
</head>
<body class="min-h-full bg-linear-to-br from-gray-50 to-gray-100 text-gray-900">
    <div class="mx-auto w-full max-w-4xl px-6 py-8">
        <a
            href="{{ route('homepage') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 transition hover:text-blue-800"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Retour à l'accueil
        </a>

        {{-- The infolist renders the title, so the page keeps it for assistive technology only. --}}
        <h1 class="sr-only">{{ $news->name }}</h1>

        <article class="mt-6 overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200">
            <header class="gradient-news flex items-center gap-3 px-8 py-5 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
                <p class="text-sm font-bold uppercase tracking-wide">Actualité</p>
            </header>

            <div class="p-8">
                {{-- Title, content, medias and metadata all come from the panel infolist. --}}
                <livewire:news.public-view :news="$news" />
            </div>
        </article>
    </div>

    @livewireScripts
    @filamentScripts
</body>
</html>
