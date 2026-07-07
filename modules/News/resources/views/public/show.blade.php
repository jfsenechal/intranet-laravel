<!DOCTYPE html>
<html lang="fr" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $news->name }} — Actualités</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        .gradient-news { background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%); }
    </style>
</head>
<body class="min-h-full bg-linear-to-br from-gray-50 to-gray-100 text-gray-900">
    <div class="mx-auto w-full max-w-3xl px-6 py-8">
        <a
            href="{{ route('homepage') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 transition hover:text-blue-800"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Retour à l'accueil
        </a>

        <article class="mt-6 overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200">
            <header class="gradient-news p-8 text-white">
                @if ($news->category)
                    <span
                        class="mb-3 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white"
                        style="background-color: {{ $news->category->color ?? '#6b7280' }}"
                    >
                        {{ $news->category->name }}
                    </span>
                @endif
                <h1 class="text-3xl font-bold leading-tight">{{ $news->name }}</h1>
                <p class="mt-3 text-sm text-blue-100">
                    {{ $news->created_at?->translatedFormat('d F Y') }}
                    @if ($news->user_add)
                        — {{ $news->user_add }}
                    @endif
                </p>
            </header>

            <div class="p-8">
                @if ($news->excerpt)
                    <p class="mb-6 text-lg font-medium text-gray-600">{{ $news->excerpt }}</p>
                @endif

                <div class="prose max-w-none">
                    {!! $news->content !!}
                </div>

                @if (! empty($news->medias))
                    <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach ($news->medias as $media)
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($media) }}"
                                alt="{{ $news->name }}"
                                class="w-full rounded-lg shadow-sm"
                            >
                        @endforeach
                    </div>
                @endif
            </div>
        </article>
    </div>
</body>
</html>
