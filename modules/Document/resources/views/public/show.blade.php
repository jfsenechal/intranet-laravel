<!DOCTYPE html>
<html lang="fr" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->name }} — Documents</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/css/filament-public.css'])
    @livewireStyles
    @filamentStyles
    <style>
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        /* Same teal as the "Resources" family of the homepage palette. */
        .gradient-documents { background: linear-gradient(135deg, #0f766e 0%, #134e4a 100%); }
    </style>
</head>
<body class="min-h-full bg-linear-to-br from-gray-50 to-gray-100 text-gray-900">
    <div class="mx-auto w-full max-w-3xl px-6 py-8">
        <a
            href="{{ route('homepage') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 transition hover:text-emerald-800"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Retour à l'accueil
        </a>

        <article class="mt-6 overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200">
            <header class="gradient-documents p-8 text-white">
                <h1 class="text-3xl font-bold leading-tight">{{ $document->name }}</h1>
                <p class="mt-3 text-sm text-emerald-100">
                    {{ $document->created_at?->translatedFormat('d F Y') }}
                    @if ($document->user_add)
                        — {{ $document->user_add }}
                    @endif
                </p>
            </header>

            <div class="p-8">
                @if ($fileUrl)
                    <a
                        href="{{ $fileUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mb-8 inline-flex items-center gap-2 rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Télécharger le document
                    </a>
                @else
                    <p class="mb-8 text-sm text-gray-500">Aucun fichier n'est associé à ce document.</p>
                @endif

                {{-- Category, description and file preview come from the panel infolist. --}}
                <livewire:document.public-view :document="$document" />
            </div>
        </article>
    </div>

    @livewireScripts
    @filamentScripts
</body>
</html>
