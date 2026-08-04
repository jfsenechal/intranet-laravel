<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de bienvenue - {{ $profile->fullName() }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; }
        html { -webkit-print-color-adjust: exact; }
    </style>
</head>
<body class="bg-white text-gray-800">
<div class="max-w-4xl mx-auto px-6 py-8 text-sm leading-relaxed">

    <header class="flex items-center gap-4 border-b-2 border-blue-700 pb-4 mb-6">
        <img src="{{ asset('images/Marche_logo.png') }}" alt="logo" class="h-16">
        <h1 class="text-xl font-bold text-blue-700">
            Informations pour votre accès au réseau de la Commune de Marche-en-Famenne
        </h1>
    </header>

    <h2 class="text-lg font-bold mb-4">{{ $profile->fullName() }}</h2>

    <section class="mb-6 bg-gray-50 rounded-lg border border-gray-200 p-4">
        <h3 class="font-bold text-green-700 mb-2">Comment débuter ?</h3>
        <p>Connectez-vous sur un PC avec le login et le mot de passe notés ci-dessous.</p>
        <p class="mt-3"><strong>Login</strong> (mail, réseau, intranet, webmail) : {{ $profile->username }}</p>
        <p><strong>Mot de passe</strong> (mail, réseau, intranet, webmail) : {{ $password }}</p>
        <p class="text-gray-600">Ce mot de passe devra être modifié à la première ouverture de session.</p>
        <p class="mt-3"><strong>Adresse mail</strong> : {{ $email ?? '—' }}</p>
        <p>Adresse du webmail : <a href="https://agenda.marche.be">https://agenda.marche.be</a></p>
        <p>Adresse de l'intranet : <a href="http://intranet.marche.be">http://intranet.marche.be</a></p>
    </section>

    @if($notes)
        <section class="mb-6">
            <h3 class="font-bold text-green-700 border-b border-gray-200 pb-1 mb-2">Remarques</h3>
            <p class="whitespace-pre-wrap">{{ $notes }}</p>
        </section>
    @endif

    <section class="mb-6">
        <h3 class="font-bold text-green-700 border-b border-gray-200 pb-1 mb-2">Dossiers sur le serveur Data</h3>
        <p>Cette personne a accès aux dossiers suivants sur le serveur (dans « Poste de travail ») :</p>
        @if($folderPaths !== [])
            <ul class="list-disc list-inside mt-2">
                @foreach($folderPaths as $path)
                    <li>{{ $path }}</li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-500">Aucun</p>
        @endif
    </section>

    <section class="mb-6">
        <h3 class="font-bold text-green-700 border-b border-gray-200 pb-1 mb-2">Membre des alias emails</h3>
        <p>Cette personne recevra les mails adressés à/aux adresse(s) suivante(s) :</p>
        @if(! empty($profile->emails))
            <ul class="list-disc list-inside mt-2">
                @foreach($profile->emails as $sharedEmail)
                    <li>{{ $sharedEmail }}</li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-500">Aucune</p>
        @endif
    </section>

    <section class="mb-6">
        <h3 class="font-bold text-green-700 border-b border-gray-200 pb-1 mb-2">Pointage</h3>
        <p>Si vous avez besoin d'un login de pointage, adressez-vous au service ressources humaines :</p>
        <ul class="list-disc list-inside mt-2">
            <li>Pascal Gaspard : 084 / 32 70 08 (9008)</li>
            <li>Bernadette Comble : 084 / 32 70 07 (9007)</li>
        </ul>
        <p class="mt-2">Un raccourci vers la plateforme de pointage se trouve sur le bureau de votre session.</p>
    </section>

    <section class="mb-6">
        <h3 class="font-bold text-green-700 border-b border-gray-200 pb-1 mb-2">Téléphonie</h3>
        @if($profile->phone && ($profile->phone->existing_number || $profile->phone->mobile_number))
            <p>Votre numéro de téléphone est le :
                {{ trim(($profile->phone->existing_number ?? '').' '.($profile->phone->mobile_number ?? '')) }}</p>
        @else
            <p class="text-gray-500">Aucune</p>
        @endif
        <p class="mt-2">Quelques fiches explicatives sur la téléphonie :
            <a href="https://formation.marche.be/?cat=33">https://formation.marche.be/?cat=33</a></p>
    </section>

    <section class="mb-6">
        <h3 class="font-bold text-green-700 border-b border-gray-200 pb-1 mb-2">
            Quelques fiches explicatives (<a href="https://formation.marche.be">https://formation.marche.be</a>)
        </h3>
        <p class="mt-2"><strong>Modifier son mot de passe</strong><br>
            <a href="https://formation.marche.be/?p=151">https://formation.marche.be/?p=151</a></p>
        <p class="mt-2"><strong>Se connecter à une imprimante réseau</strong><br>
            <a href="https://formation.marche.be/?p=236">https://formation.marche.be/?p=236</a></p>
        <p class="mt-2"><strong>Configurer son code d'autorisation d'impression</strong><br>
            <a href="https://formation.marche.be/?p=545">https://formation.marche.be/?p=545</a></p>
        <p class="mt-2"><strong>Configurer sa boite mail dans Thunderbird</strong><br>
            <a href="https://formation.marche.be/?p=160">https://formation.marche.be/?p=160</a></p>
        <p class="mt-2"><strong>Configurer son message d'absence</strong><br>
            <a href="https://formation.marche.be/?p=581">https://formation.marche.be/?p=581</a></p>
    </section>

    <section class="mb-6">
        <h3 class="font-bold text-green-700 border-b border-gray-200 pb-1 mb-2">
            Comment obtenir de l'aide sur les systèmes informatiques ?
        </h3>
        <p><strong>Pour les problèmes techniques et les demandes de matériel :</strong></p>
        <p class="mt-2">Contactez-nous par mail sur {{ config('agent.informatique_email') ?: 'informatique@marche.be' }}</p>
        <p class="mt-2">Contactez-nous par téléphone :</p>
        <ul class="list-disc list-inside">
            <li>Pierre 9366 (084 / 84 03 66)</li>
            <li>Philippe 9088 (084 / 32 70 88)</li>
            <li>Nicolas 9089 (084 / 32 70 89)</li>
        </ul>
        <p class="mt-3"><strong>Pour les formations : Jean-Philippe Brasseur (9056) et Martine Giet (9054)</strong></p>
        <p class="mt-2"><strong>Pour un accès spécial sur l'intranet, adressez-vous à votre chef de service</strong></p>
        <p class="mt-2"><strong>Pour un accès vers Plone Meeting, adressez-vous à M. Brasseur au 9056.</strong></p>
        <p class="mt-2">Pour un accès au programme de comptabilité, bons de commande, taxes : contactez Laurent
            Chamberland au 9081.</p>
    </section>

    <footer class="border-t border-gray-200 pt-3 text-xs text-gray-500">
        Espace public numérique (EPN)<br>
        Rue Victor Libert, 36<br>
        6900 Marche-en-Famenne
    </footer>

</div>
</body>
</html>
