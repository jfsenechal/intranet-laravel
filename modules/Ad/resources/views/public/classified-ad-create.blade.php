<!DOCTYPE html>
<html lang="fr" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Déposer une petite annonce</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full bg-gray-50 text-gray-900">
    <div class="max-w-2xl mx-auto px-4 py-12 space-y-8">
        <header class="text-center">
            <h1 class="text-3xl font-bold">Déposer une petite annonce</h1>
            <p class="mt-2 text-gray-600">
                Votre annonce sera envoyée par email à l'ensemble du personnel.
            </p>
        </header>

        @if (session('success'))
            <div class="rounded-md bg-green-50 border border-green-200 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-md bg-red-50 border border-red-200 p-4 text-red-800 space-y-2">
                <p>{{ session('error') }}</p>
                @if (session('moderation_reasons'))
                    <ul class="list-disc list-inside text-sm">
                        @foreach (session('moderation_reasons') as $reason)
                            <li>{{ $reason }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-md bg-red-50 border border-red-200 p-4 text-red-800">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($employee === null)
            <section class="bg-white shadow-sm rounded-lg p-6 space-y-4">
                <h2 class="text-xl font-semibold">Identifiez-vous</h2>
                <p class="text-sm text-gray-600">
                    Renseignez votre email professionnel ou privé.
                    Seuls les agents en contrat actif peuvent déposer une annonce.
                </p>

                <form action="{{ route('ad.public.identify') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label for="identify-email" class="block text-sm font-medium">Email</label>
                        <input
                            id="identify-email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                    </div>
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-md"
                    >
                        Continuer
                    </button>
                </form>
            </section>
        @else
            <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-gray-600">
                <span>Annonce déposée par <strong>{{ $employee->full_name }}</strong> ({{ $email }})</span>
                <form action="{{ route('ad.public.forget') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-pink-600 hover:underline">Changer d'email</button>
                </form>
            </div>

            <div class="rounded-md bg-amber-50 border border-amber-200 p-4 text-amber-900 text-sm">
                <p class="font-semibold">Règles de bonne conduite</p>
                <p>
                    Votre annonce sera lue par l'ensemble du personnel. Rédigez-la dans le respect de chacun :
                    les insultes, propos discriminants ou injurieux et tout contenu à caractère sexuel sont interdits.
                </p>
            </div>

            <section class="bg-white shadow-sm rounded-lg p-6">
                <form action="{{ route('ad.public.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="ad-name" class="block text-sm font-medium">Titre</label>
                        <input
                            id="ad-name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            maxlength="255"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                        >
                    </div>
                    <div>
                        <label for="ad-content" class="block text-sm font-medium">Contenu</label>
                        <textarea
                            id="ad-content"
                            name="content"
                            rows="8"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                        >{{ old('content') }}</textarea>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="ad-category" class="block text-sm font-medium">Catégorie</label>
                            <select
                                id="ad-category"
                                name="category_id"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                                <option value="">Choisir…</option>
                                @foreach ($categories as $id => $name)
                                    <option value="{{ $id }}" @selected((string) old('category_id') === (string) $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="ad-end-date" class="block text-sm font-medium">Date de fin de publication</label>
                            <input
                                id="ad-end-date"
                                type="date"
                                name="end_date"
                                value="{{ old('end_date', now()->addWeeks(2)->toDateString()) }}"
                                min="{{ now()->toDateString() }}"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                            >
                        </div>
                    </div>
                    <div>
                        <label for="ad-medias" class="block text-sm font-medium">Pièces jointes (5 maximum, 10 Mo chacune)</label>
                        <input
                            id="ad-medias"
                            type="file"
                            name="medias[]"
                            multiple
                            class="mt-1 block w-full text-sm"
                        >
                    </div>
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-md"
                    >
                        Publier l'annonce
                    </button>
                </form>
            </section>
        @endif
    </div>
</body>
</html>
