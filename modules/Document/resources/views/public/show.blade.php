<!DOCTYPE html>
<html lang="fr" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->name }} — Documents</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        .gradient-documents { background: linear-gradient(135deg, #10b981 0%, #047857 100%); }
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
                @if ($document->category)
                    <span class="mb-3 inline-flex items-center rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white backdrop-blur">
                        {{ $document->category->name }}
                    </span>
                @endif
                <h1 class="text-3xl font-bold leading-tight">{{ $document->name }}</h1>
                <p class="mt-3 text-sm text-emerald-100">
                    {{ $document->created_at?->translatedFormat('d F Y') }}
                    @if ($document->user_add)
                        — {{ $document->user_add }}
                    @endif
                </p>
            </header>

            <div class="p-8">
                @if ($document->content)
                    <div class="prose max-w-none">
                        {!! $document->content !!}
                    </div>
                @endif

                @if ($fileUrl)
                    <div class="mt-8">
                        <a
                            href="{{ $fileUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Télécharger le document
                        </a>

                        @if ($document->file_mime === 'application/pdf')
                            <iframe
                                src="{{ $fileUrl }}"
                                title="{{ $document->name }}"
                                class="mt-6 w-full rounded-lg ring-1 ring-gray-200"
                                style="min-height: 80svh"
                            ></iframe>
                        @elseif (str_starts_with((string) $document->file_mime, 'image/'))
                            <img
                                src="{{ $fileUrl }}"
                                alt="{{ $document->name }}"
                                class="mt-6 w-full rounded-lg shadow-sm"
                            >
                        @endif
                    </div>
                @else
                    <p class="mt-8 text-sm text-gray-500">Aucun fichier n'est associé à ce document.</p>
                @endif
            </div>
        </article>
    </div>
</body>
</html>
