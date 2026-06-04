<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- En attente --}}
        <x-filament::section icon="heroicon-o-clock" icon-color="warning">
            <x-slot name="heading">En attente</x-slot>
            <x-slot name="description">Tickets sans guichet assigné</x-slot>
            <x-slot name="headerEnd">
                <x-filament::badge color="warning">{{ $pending->count() }}</x-filament::badge>
            </x-slot>

            @forelse ($pending as $ticket)
                <div @class([
                    'flex items-start justify-between gap-3 py-3 text-sm',
                    'border-t border-gray-100 dark:border-white/10' => ! $loop->first,
                ])>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold tabular-nums">#{{ $ticket->number }}</span>
                            <x-filament::badge color="gray" size="sm">{{ $ticket->service }}</x-filament::badge>
                        </div>
                        <p class="mt-1 truncate text-gray-500 dark:text-gray-400">{{ $ticket->reason }}</p>
                    </div>
                    <div class="flex shrink-0 flex-col items-end gap-2">
                        <span class="tabular-nums text-gray-400">{{ $ticket->createdAt?->format('H:i') }}</span>
                        <div class="flex items-center gap-1">
                            {{ ($this->assignOfficeAction)(['ticket' => $ticket->id]) }}
                            {{ ($this->cancelTicketAction)(['ticket' => $ticket->id]) }}
                        </div>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-gray-400">Aucun ticket en attente.</p>
            @endforelse
        </x-filament::section>

        {{-- En traitement --}}
        <x-filament::section icon="heroicon-o-arrow-path" icon-color="info">
            <x-slot name="heading">En traitement</x-slot>
            <x-slot name="description">Tickets pris en charge à un guichet</x-slot>
            <x-slot name="headerEnd">
                <x-filament::badge color="info">{{ $processing->count() }}</x-filament::badge>
            </x-slot>

            @forelse ($processing as $ticket)
                <div @class([
                    'flex items-start justify-between gap-3 py-3 text-sm',
                    'border-t border-gray-100 dark:border-white/10' => ! $loop->first,
                ])>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold tabular-nums">#{{ $ticket->number }}</span>
                            <x-filament::badge color="gray" size="sm">{{ $ticket->service }}</x-filament::badge>
                        </div>
                        <p class="mt-1 truncate text-gray-500 dark:text-gray-400">{{ $ticket->reason }}</p>
                    </div>
                    <div class="flex shrink-0 flex-col items-end gap-2 text-right">
                        <x-filament::badge color="info" size="sm">{{ $ticket->office?->name ?? '—' }}</x-filament::badge>
                        <span class="tabular-nums text-gray-400">{{ $ticket->createdAt?->format('H:i') }}</span>
                        {{ ($this->cancelTicketAction)(['ticket' => $ticket->id]) }}
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-gray-400">Aucun ticket en traitement.</p>
            @endforelse
        </x-filament::section>
    </div>

    @script
    <script>
        const VAPID_PUBLIC_KEY = @js(config('webpush.vapid.public_key'));
        const SOUND_URL = @js(asset('storage/456966__funwithsound__success-fanfare-trumpets.mp3'));
        const LOGO_URL = '/images/Marche_logo.png';

        const playSound = () => {
            try {
                new Audio(SOUND_URL).play().catch(() => {});
            } catch (e) {}
        };

        const showNotification = (title, body) => {
            if (! ('Notification' in window) || Notification.permission !== 'granted') {
                return;
            }
            new Notification(title, { body, icon: LOGO_URL });
        };

        // 1. Real-time updates while the tab is open (Reverb / Echo).
        const subscribeEcho = () => {
            if (! window.Echo) {
                setTimeout(subscribeEcho, 500);
                return;
            }

            window.Echo.private('guichet-hdv.tickets')
                .listen('.ticket.assigned', (e) => {
                    playSound();
                    showNotification(
                        'Ticket assigné',
                        `Ticket #${e.number} (${e.service}) → guichet ${e.office ?? '—'}`,
                    );
                    $wire.dispatch('tickets-updated');
                })
                .listen('.ticket.cancelled', () => {
                    $wire.dispatch('tickets-updated');
                });
        };

        // 2. Web Push registration for closed-tab delivery.
        const urlBase64ToUint8Array = (base64String) => {
            const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const raw = atob(base64);
            return Uint8Array.from([...raw].map((char) => char.charCodeAt(0)));
        };

        const registerPush = async () => {
            if (! ('serviceWorker' in navigator) || ! ('PushManager' in window) || ! VAPID_PUBLIC_KEY) {
                return;
            }

            try {
                const registration = await navigator.serviceWorker.register('/sw.js');
                const permission = await Notification.requestPermission();

                if (permission !== 'granted') {
                    return;
                }

                let subscription = await registration.pushManager.getSubscription();

                if (! subscription) {
                    subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
                    });
                }

                $wire.storePushSubscription(subscription.toJSON());
            } catch (e) {}
        };

        subscribeEcho();
        registerPush();
    </script>
    @endscript
</x-filament-panels::page>
