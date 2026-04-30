@php
    use AcMarche\Ad\Filament\Resources\ClassifiedAd\ClassifiedAdResource;
    use AcMarche\Document\Filament\Resources\Documents\DocumentResource;
    use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
    use AcMarche\News\Filament\Resources\News\NewsResource;
    use Illuminate\Support\Facades\Storage;
@endphp
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Intranet — Accueil</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

        .gradient-sport { background: linear-gradient(135deg, #f97316 0%, #ef4444 50%, #ec4899 100%); }
        .gradient-birthday { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #ef4444 100%); }
        .gradient-news { background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%); }
        .gradient-documents { background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%); }
        .gradient-classified { background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); }
        .gradient-employee { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); }
        .gradient-rss { background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%); }
        .gradient-press { background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%); }
        .gradient-hero { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #6366f1 100%); }

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
<body class="min-h-full bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900">

    <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/80 backdrop-blur-md">
        <div class="mx-auto flex max-w-screen-2xl items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-bold shadow-md">
                    M
                </div>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-gray-500">Intranet</p>
                    <p class="text-lg font-bold text-gray-900">Ville de Marche-en-Famenne</p>
                </div>
            </div>
            <a
                href="{{ url('/app/login') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-blue-700 hover:shadow-lg"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Se connecter
            </a>
        </div>
    </header>

    <main class="mx-auto w-full max-w-screen-2xl space-y-8 px-6 py-8">
        {{-- Hero banner --}}
        <div class="relative overflow-hidden rounded-3xl gradient-hero p-10 text-white shadow-2xl animate-fade-in-up">
            <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-10 h-72 w-72 rounded-full bg-white/5 blur-3xl"></div>
            <div class="relative">
                <p class="text-sm font-medium uppercase tracking-wider opacity-80">
                    {{ \Illuminate\Support\Carbon::now()->translatedFormat('l d F Y') }}
                </p>
                <h1 class="mt-2 text-4xl font-extrabold md:text-6xl">Bienvenue sur l'intranet</h1>
                <p class="mt-3 max-w-2xl text-lg opacity-90 md:text-xl">
                    Votre portail central pour les actualités, documents et informations du personnel.
                </p>
            </div>
        </div>

        {{-- Main grid: News (left, wide) + sidebar widgets (right) --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- LEFT column: Latest news (spans 2/3) --}}
            <div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up lg:col-span-2" style="--delay: 0.1s">
                <div class="gradient-news flex items-center justify-between p-5 text-white">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 backdrop-blur">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold">Dernières actualités</h2>
                    </div>
                    <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold backdrop-blur">
                        {{ $latestNews->count() }}
                    </span>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($latestNews as $index => $news)
                        <a
                            href="{{ NewsResource::getUrl('view', ['record' => $news->id], panel: 'news') }}"
                            class="group flex items-start gap-3 p-4 transition hover:bg-gray-50 animate-fade-in-up"
                            style="--delay: {{ 0.15 + ($index * 0.05) }}s"
                        >
                            <div class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-blue-500 group-hover:animate-pulse"></div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-gray-900 group-hover:text-blue-600">
                                    {{ $news->title ?? $news->name }}
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    {{ $news->created_at?->translatedFormat('d F Y') }}
                                    @if ($news->user_add)
                                        — {{ $news->user_add }}
                                    @endif
                                </p>
                            </div>
                        </a>
                    @empty
                        <p class="p-6 text-center text-sm text-gray-500">Aucune actualité récente.</p>
                    @endforelse
                </div>
            </div>

            {{-- RIGHT column: stacked widgets --}}
            <div class="space-y-6">
                {{-- Birthdays --}}
                <div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up" style="--delay: 0.15s">
                    <div class="gradient-birthday flex items-center gap-3 p-4 text-white">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A2.704 2.704 0 003 15.546V21h18v-5.454zM12 3v2m0 2a2 2 0 100-4 2 2 0 000 4zm0 0v6m-3 4h6" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold">Anniversaires</h2>
                            <p class="text-xs opacity-90">
                                {{ \Illuminate\Support\Carbon::today()->translatedFormat('d F') }}
                            </p>
                        </div>
                    </div>
                    <div class="p-4">
                        @forelse ($todayBirthdays as $index => $employee)
                            <div class="flex items-center gap-3 border-b border-gray-100 py-2 last:border-0 animate-fade-in-up" style="--delay: {{ 0.2 + ($index * 0.05) }}s">
                                <div class="relative">
                                    @if ($employee->photo && Storage::disk('public')->exists($employee->photo))
                                        <img
                                            src="{{ Storage::disk('public')->url($employee->photo) }}"
                                            alt="{{ $employee->first_name }} {{ $employee->last_name }}"
                                            class="h-10 w-10 rounded-full object-cover ring-2 ring-amber-400"
                                        />
                                    @else
                                        <img
                                            src="https://ui-avatars.com/api/?size=128&background=fbbf24&color=fff&name={{ urlencode(trim($employee->first_name.' '.$employee->last_name)) }}"
                                            alt="{{ $employee->first_name }} {{ $employee->last_name }}"
                                            class="h-10 w-10 rounded-full object-cover ring-2 ring-amber-400"
                                        />
                                    @endif
                                    <span class="absolute -right-1 -top-1 text-sm animate-float">🎂</span>
                                </div>
                                <p class="min-w-0 flex-1 truncate text-sm font-semibold text-gray-900">
                                    {{ $employee->first_name }} {{ $employee->last_name }}
                                </p>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-4 text-center">
                                <span class="text-2xl">🎈</span>
                                <p class="mt-1 text-xs text-gray-500">
                                    Aucun anniversaire aujourd'hui.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Sport activities --}}
                <div class="card-hover relative overflow-hidden rounded-2xl shadow-lg animate-fade-in-up" style="--delay: 0.2s">
                    <div class="gradient-sport absolute inset-0"></div>
                    <div class="relative flex flex-col gap-3 p-5 text-white">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur animate-float">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-extrabold leading-tight">Activités sportives</h2>
                        </div>
                        <p class="text-sm opacity-90">Pour le personnel</p>
                        <div class="flex items-center gap-3 text-3xl">
                            <span class="animate-float">⚽</span>
                            <span class="animate-float" style="animation-delay: 0.3s">🏃</span>
                            <span class="animate-float" style="animation-delay: 0.6s">🏋️</span>
                            <span class="animate-float" style="animation-delay: 0.9s">🚴</span>
                        </div>
                    </div>
                </div>

                {{-- Classified ads --}}
                <div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up" style="--delay: 0.25s">
                    <div class="gradient-classified flex items-center justify-between p-4 text-white">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <h2 class="text-base font-bold">Petites annonces</h2>
                        </div>
                        <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs font-semibold backdrop-blur">
                            {{ $latestAds->count() }}
                        </span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($latestAds as $index => $ad)
                            <a
                                href="{{ ClassifiedAdResource::getUrl('view', ['record' => $ad->id], panel: 'ad') }}"
                                class="group block p-3 transition hover:bg-gray-50 animate-fade-in-up"
                                style="--delay: {{ 0.3 + ($index * 0.05) }}s"
                            >
                                <p class="truncate text-sm font-medium text-gray-900 group-hover:text-pink-600">
                                    {{ $ad->title ?? $ad->name }}
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    {{ $ad->created_at?->translatedFormat('d F Y') }}
                                </p>
                            </a>
                        @empty
                            <p class="p-4 text-center text-xs text-gray-500">Aucune annonce.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Latest employees --}}
                <div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up" style="--delay: 0.3s">
                    <div class="gradient-employee flex items-center justify-between p-4 text-white">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <h2 class="text-base font-bold">Nouveaux agents</h2>
                        </div>
                        <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs font-semibold backdrop-blur">
                            {{ $latestEmployees->count() }}
                        </span>
                    </div>
                    <div class="p-4">
                        @forelse ($latestEmployees as $index => $employee)
                            <a
                                href="{{ EmployeeResource::getUrl('view', ['record' => $employee->id], panel: 'hrm-panel') }}"
                                class="group flex items-center gap-3 border-b border-gray-100 py-2 last:border-0 animate-fade-in-up"
                                style="--delay: {{ 0.35 + ($index * 0.05) }}s"
                            >
                                @if ($employee->photo && Storage::disk('public')->exists($employee->photo))
                                    <img
                                        src="{{ Storage::disk('public')->url($employee->photo) }}"
                                        alt="{{ $employee->first_name }} {{ $employee->last_name }}"
                                        class="h-10 w-10 rounded-full object-cover ring-2 ring-indigo-300"
                                    />
                                @else
                                    <img
                                        src="https://ui-avatars.com/api/?size=128&background=6366f1&color=fff&name={{ urlencode(trim($employee->first_name.' '.$employee->last_name)) }}"
                                        alt="{{ $employee->first_name }} {{ $employee->last_name }}"
                                        class="h-10 w-10 rounded-full object-cover ring-2 ring-indigo-300"
                                    />
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-gray-900 group-hover:text-indigo-600">
                                        {{ $employee->first_name }} {{ $employee->last_name }}
                                    </p>
                                    @if ($employee->hired_at)
                                        <p class="text-xs text-gray-500">
                                            engagé le {{ $employee->hired_at->translatedFormat('d F Y') }}
                                        </p>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <p class="text-center text-xs text-gray-500">Aucun nouvel agent.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Second row: Documents (left) + Press (right) --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Latest documents --}}
            <div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up" style="--delay: 0.35s">
                <div class="gradient-documents flex items-center justify-between p-5 text-white">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 backdrop-blur">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold">Derniers documents utilisés</h2>
                    </div>
                    <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold backdrop-blur">
                        {{ $latestDocuments->count() }}
                    </span>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($latestDocuments as $index => $document)
                        <a
                            href="{{ DocumentResource::getUrl('view', ['record' => $document->id], panel: 'document-panel') }}"
                            class="group flex items-start gap-3 p-4 transition hover:bg-gray-50 animate-fade-in-up"
                            style="--delay: {{ 0.4 + ($index * 0.05) }}s"
                        >
                            <div class="mt-1 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 transition-transform group-hover:scale-110">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-gray-900 group-hover:text-emerald-600">
                                    {{ $document->name }}
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    {{ $document->created_at?->translatedFormat('d F Y') }}
                                    @if ($document->file_name)
                                        — {{ $document->file_name }}
                                    @endif
                                </p>
                            </div>
                        </a>
                    @empty
                        <p class="p-6 text-center text-sm text-gray-500">Aucun document récent.</p>
                    @endforelse
                </div>
            </div>

            {{-- Press release --}}
            <div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up" style="--delay: 0.4s">
                <div class="gradient-press flex items-center gap-3 p-5 text-white">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 backdrop-blur">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold">Revue de presse</h2>
                </div>
                <div class="max-h-[500px] divide-y divide-gray-100 overflow-y-auto">
                    @forelse ($pressArticles as $index => $article)
                        <a
                            href="{{ $article['url'] ?? $article['link'] ?? '#' }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="group block p-4 transition hover:bg-gray-50 animate-fade-in-up"
                            style="--delay: {{ 0.45 + ($index * 0.03) }}s"
                        >
                            <p class="line-clamp-2 text-sm font-semibold text-gray-900 group-hover:text-cyan-600">
                                {{ $article['title'] ?? $article['name'] ?? 'Article' }}
                            </p>
                            @if (! empty($article['source']) || ! empty($article['publisher']))
                                <p class="mt-1 text-xs font-medium uppercase tracking-wide text-cyan-600">
                                    {{ $article['source'] ?? $article['publisher'] }}
                                </p>
                            @endif
                            @if (! empty($article['publishedAt']) || ! empty($article['published_at']) || ! empty($article['date']))
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ \Illuminate\Support\Carbon::parse($article['publishedAt'] ?? $article['published_at'] ?? $article['date'])->translatedFormat('d F Y') }}
                                </p>
                            @endif
                        </a>
                    @empty
                        <p class="p-6 text-center text-sm text-gray-500">Aucun article de presse disponible.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Bottom row: RSS feeds (full width) --}}
        <div class="card-hover overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 animate-fade-in-up" style="--delay: 0.45s">
            <div class="gradient-rss flex items-center justify-between p-5 text-white">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 backdrop-blur">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M5.5 18a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM.5 10.5v3.5a9 9 0 019 9h3.5A12.5 12.5 0 00.5 10.5zm0-7v3.5A16 16 0 0116.5 23H20A19.5 19.5 0 00.5 3.5z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold">Flux d'actualité</h2>
                </div>
                <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold backdrop-blur">
                    {{ count($rssItems) }}
                </span>
            </div>
            <div class="grid grid-cols-1 divide-y divide-gray-100 md:grid-cols-2 md:divide-y-0 md:divide-x lg:grid-cols-3">
                @forelse ($rssItems as $index => $item)
                    <a
                        href="{{ $item['link'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group block p-4 transition hover:bg-gray-50 animate-fade-in-up"
                        style="--delay: {{ 0.5 + ($index * 0.03) }}s"
                    >
                        <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-purple-600">
                            {{ $item['source'] }}
                        </p>
                        <p class="line-clamp-2 text-sm font-medium text-gray-900 group-hover:text-purple-700">
                            {{ $item['title'] }}
                        </p>
                        @if ($item['date'])
                            <p class="mt-1 text-xs text-gray-500">
                                {{ \Illuminate\Support\Carbon::parse($item['date'])->translatedFormat('d F Y à H:i') }}
                            </p>
                        @endif
                    </a>
                @empty
                    <p class="col-span-full p-6 text-center text-sm text-gray-500">Aucun flux disponible.</p>
                @endforelse
            </div>
        </div>
    </main>

    <footer class="mt-12 border-t border-gray-200 bg-white">
        <div class="mx-auto max-w-screen-2xl px-6 py-6 text-center text-sm text-gray-500">
            © {{ date('Y') }} Ville de Marche-en-Famenne — Intranet
        </div>
    </footer>

</body>
</html>
