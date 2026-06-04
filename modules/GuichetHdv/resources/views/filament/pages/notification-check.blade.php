<x-filament-panels::page>
    <div
        x-data="{
            supported: ('Notification' in window) && ('serviceWorker' in navigator) && ('PushManager' in window),
            permission: ('Notification' in window) ? Notification.permission : 'unsupported',
            swReady: false,
            subscribed: false,
            busy: false,
            vapid: @js(config('webpush.vapid.public_key')),
            soundUrl: @js(asset('storage/456966__funwithsound__success-fanfare-trumpets.mp3')),
            logoUrl: '/images/Marche_logo.png',

            async init() {
                await this.refresh();
            },

            async refresh() {
                this.permission = ('Notification' in window) ? Notification.permission : 'unsupported';

                if ('serviceWorker' in navigator) {
                    const registration = await navigator.serviceWorker.getRegistration();
                    this.swReady = !! registration;
                    this.subscribed = registration ? !! (await registration.pushManager.getSubscription()) : false;
                }
            },

            async enable() {
                if (! this.supported) {
                    return;
                }

                this.busy = true;

                try {
                    this.permission = await Notification.requestPermission();

                    if (this.permission === 'granted') {
                        await this.subscribe();
                    }
                } finally {
                    this.busy = false;
                    await this.refresh();
                }
            },

            async subscribe() {
                if (! this.vapid) {
                    return;
                }

                const registration = await navigator.serviceWorker.register('/sw.js');
                let subscription = await registration.pushManager.getSubscription();

                if (! subscription) {
                    subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: this.urlB64ToUint8Array(this.vapid),
                    });
                }

                $wire.storePushSubscription(subscription.toJSON());
            },

            test() {
                try {
                    new Audio(this.soundUrl).play().catch(() => {});
                } catch (e) {}

                if (this.permission === 'granted') {
                    new Notification('Test de notification', {
                        body: 'Les notifications du guichet fonctionnent ✅',
                        icon: this.logoUrl,
                    });
                }
            },

            urlB64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
                const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                const raw = atob(base64);
                return Uint8Array.from([...raw].map((char) => char.charCodeAt(0)));
            },
        }"
        class="space-y-6"
    >
        <x-filament::section icon="heroicon-o-bell-alert">
            <x-slot name="heading">État des notifications</x-slot>
            <x-slot name="description">
                Vérifiez que votre navigateur autorise les notifications du guichet.
            </x-slot>

            <dl class="divide-y divide-gray-100 dark:divide-white/10 text-sm">
                <div class="flex items-center justify-between py-3">
                    <dt class="text-gray-500 dark:text-gray-400">Navigateur compatible</dt>
                    <dd>
                        <template x-if="supported">
                            <x-filament::badge color="success">Oui</x-filament::badge>
                        </template>
                        <template x-if="! supported">
                            <x-filament::badge color="danger">Non</x-filament::badge>
                        </template>
                    </dd>
                </div>

                <div class="flex items-center justify-between py-3">
                    <dt class="text-gray-500 dark:text-gray-400">Autorisation</dt>
                    <dd>
                        <template x-if="permission === 'granted'">
                            <x-filament::badge color="success">Accordée</x-filament::badge>
                        </template>
                        <template x-if="permission === 'default'">
                            <x-filament::badge color="warning">Non demandée</x-filament::badge>
                        </template>
                        <template x-if="permission === 'denied'">
                            <x-filament::badge color="danger">Refusée</x-filament::badge>
                        </template>
                        <template x-if="permission === 'unsupported'">
                            <x-filament::badge color="gray">Non supportée</x-filament::badge>
                        </template>
                    </dd>
                </div>

                <div class="flex items-center justify-between py-3">
                    <dt class="text-gray-500 dark:text-gray-400">Service worker</dt>
                    <dd>
                        <template x-if="swReady">
                            <x-filament::badge color="success">Actif</x-filament::badge>
                        </template>
                        <template x-if="! swReady">
                            <x-filament::badge color="gray">Inactif</x-filament::badge>
                        </template>
                    </dd>
                </div>

                <div class="flex items-center justify-between py-3">
                    <dt class="text-gray-500 dark:text-gray-400">Abonnement push (hors ligne)</dt>
                    <dd>
                        <template x-if="subscribed">
                            <x-filament::badge color="success">Abonné</x-filament::badge>
                        </template>
                        <template x-if="! subscribed">
                            <x-filament::badge color="gray">Non abonné</x-filament::badge>
                        </template>
                    </dd>
                </div>
            </dl>

            <div class="mt-6 flex flex-wrap gap-3">
                <x-filament::button
                    icon="heroicon-o-bell"
                    x-show="permission !== 'granted' && permission !== 'unsupported'"
                    x-bind:disabled="busy || ! supported"
                    x-on:click="enable()"
                >
                    Activer les notifications
                </x-filament::button>

                <x-filament::button
                    color="gray"
                    icon="heroicon-o-speaker-wave"
                    x-show="permission === 'granted'"
                    x-on:click="test()"
                >
                    Tester (son + notification)
                </x-filament::button>

                <x-filament::button
                    color="info"
                    icon="heroicon-o-arrow-path"
                    x-show="permission === 'granted' && ! subscribed"
                    x-bind:disabled="busy"
                    x-on:click="enable()"
                >
                    Réabonner ce navigateur
                </x-filament::button>
            </div>

            <p
                x-show="permission === 'denied'"
                x-cloak
                class="mt-4 text-sm text-danger-600 dark:text-danger-400"
            >
                Les notifications sont bloquées pour ce site. Réautorisez-les dans les paramètres
                du navigateur (icône du cadenas dans la barre d'adresse), puis rechargez la page.
            </p>
        </x-filament::section>
    </div>
</x-filament-panels::page>
