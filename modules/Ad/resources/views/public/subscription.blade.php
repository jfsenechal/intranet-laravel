<!DOCTYPE html>
<html lang="fr" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Abonnement aux petites annonces</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full bg-gray-50 text-gray-900">
    <div class="max-w-2xl mx-auto px-4 py-12 space-y-8">
        <header class="text-center">
            <h1 class="text-3xl font-bold">Abonnement aux petites annonces</h1>
            <p class="mt-2 text-gray-600">
                Recevez un email lors de la publication d'une nouvelle annonce.
            </p>
        </header>

        @if (session('success'))
            <div class="rounded-md bg-green-50 border border-green-200 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-md bg-red-50 border border-red-200 p-4 text-red-800">
                {{ session('error') }}
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

        <section class="bg-white shadow-sm rounded-lg p-6 space-y-4">
            <h2 class="text-xl font-semibold">S'abonner</h2>
            <p class="text-sm text-gray-600">
                Renseignez votre email professionnel ou privé.
                Seuls les agents en contrat actif peuvent s'abonner.
            </p>

            <form action="{{ route('ad.subscription.subscribe') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label for="subscribe-email" class="block text-sm font-medium">Email</label>
                    <input
                        id="subscribe-email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                    >
                </div>
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-md"
                >
                    S'abonner
                </button>
            </form>
        </section>

        <section class="bg-white shadow-sm rounded-lg p-6 space-y-4">
            <h2 class="text-xl font-semibold">Se désabonner</h2>
            <form action="{{ route('ad.subscription.unsubscribe') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label for="unsubscribe-email" class="block text-sm font-medium">Email</label>
                    <input
                        id="unsubscribe-email"
                        type="email"
                        name="email"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                    >
                </div>
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md"
                >
                    Se désabonner
                </button>
            </form>
        </section>
    </div>
</body>
</html>
