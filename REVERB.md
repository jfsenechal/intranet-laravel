
  What was implemented

  Transport: Reverb + Echo (open tab) + Web Push/VAPID (closed tab)
  
  Backend
  - composer require laravel/reverb laravel-notification-channels/webpush; ran reverb:install, published config/broadcasting.php, config/reverb.php, config/webpush.php, routes/channels.php, generated VAPID keys, ran the
  push_subscriptions migration.
  - .env: BROADCAST_CONNECTION=reverb, Reverb + VITE_REVERB_*, VAPID_* + VITE_VAPID_PUBLIC_KEY.
  - config/filament.php → enabled the broadcasting.echo block for Reverb (Filament bundles the Echo client, so window.Echo is available inside the panel).
  - Events TicketAssigned / TicketCancelled (ShouldBroadcastNow) on private channel guichet-hdv.tickets; channel authorized in routes/channels.php for ROLE_GUICHET_AGENT/ROLE_GUICHET.
  - TicketAssignedPush web-push notification (logo icon, links back to the page).
  - User now uses HasPushSubscriptions.
  - TicketsOfTheDay: assign → dispatches TicketAssigned + web-pushes other agents; cancel → dispatches TicketCancelled; #[On('tickets-updated')] re-renders the lists; storePushSubscription() Livewire method persists the browser
  subscription (no extra route needed).

  Frontend
  - public/sw.js service worker (push → OS notification with logo; click → focus/open the dashboard).
  - @script block in the page: subscribes to the Echo channel → on assign plays the .mp3 + shows an in-page Notification with the logo and dispatches tickets-updated; on cancel just refreshes. Also registers the service worker
  + push subscription.
  - Fixed a dangling public/storage symlink (left over from an intranet2 move) so the mp3 actually serves at /storage/....
  
  Tests: 56 pass, including new ones for both broadcast events, the web-push-to-other-agents, and subscription storage. Set BROADCAST_CONNECTION=null in phpunit.xml so tests don't hit a live websocket.

  To run it locally   

  1. Start Reverb: php artisan reverb:start (separate long-running process).
  2. Build assets: npm run build (or composer run dev) so the Filament Echo config is picked up.
  - config/filament.php → enabled the broadcasting.echo block for Reverb (Filament bundles the Echo client, so window.Echo is available inside the panel).
  - Events TicketAssigned / TicketCancelled (ShouldBroadcastNow) on private channel guichet-hdv.tickets; channel authorized in routes/channels.php for ROLE_GUICHET_AGENT/ROLE_GUICHET.
  - TicketAssignedPush web-push notification (logo icon, links back to the page).
  - User now uses HasPushSubscriptions.
  - TicketsOfTheDay: assign → dispatches TicketAssigned + web-pushes other agents; cancel → dispatches TicketCancelled; #[On('tickets-updated')] re-renders the lists; storePushSubscription() Livewire method persists the browser
  subscription (no extra route needed).

  Frontend
  - public/sw.js service worker (push → OS notification with logo; click → focus/open the dashboard).
  - @script block in the page: subscribes to the Echo channel → on assign plays the .mp3 + shows an in-page Notification with the logo and dispatches tickets-updated; on cancel just refreshes. Also registers the service worker
  + push subscription.
  - Fixed a dangling public/storage symlink (left over from an intranet2 move) so the mp3 actually serves at /storage/....

  Tests: 56 pass, including new ones for both broadcast events, the web-push-to-other-agents, and subscription storage. Set BROADCAST_CONNECTION=null in phpunit.xml so tests don't hit a live websocket.

  To run it locally

  1. Start Reverb: php artisan reverb:start (separate long-running process).
  2. Build assets: npm run build (or composer run dev) so the Filament Echo config is picked up.
  3. Open Tickets du jour as a user with ROLE_GUICHET_AGENT/ROLE_GUICHET; the browser will prompt for notification permission (needed for both layers).

  Behavior recap, matching the hard browser limits we discussed:
  - Tab open: instant list refresh + fanfare .mp3 + logo notification.
  - Tab closed: OS push notification with the logo icon (OS default sound — the custom mp3 can't play without an open page).

  Want me to add php artisan reverb:start to the composer run dev/concurrently script so it starts alongside Vite in development?

## Production: serving Reverb over `wss://`

Reverb listens as a **plain `ws://` server on `127.0.0.1:8080`**. Browsers block insecure `ws://` from an HTTPS page, so in production your web server must terminate TLS and reverse-proxy the websocket path (`/app/...`) to the local Reverb port. The browser only ever talks `wss://your-domain` on port 443; the proxy forwards it to `ws://127.0.0.1:8080`.

```
Browser ──wss://your-domain/app/KEY──► Web server (HTTPS :443) ──ws://127.0.0.1:8080──► Reverb
```

### Production `.env`

The `VITE_*` values are compiled into the JS at `npm run build`, so the browser's Echo client must point at the public domain over TLS — **not** `localhost:8080`:

```dotenv
BROADCAST_CONNECTION=reverb

# Internal: how the app server publishes events to Reverb (plain http, loopback)
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

# What the browser's Echo client connects to (public domain, TLS)
VITE_REVERB_HOST="your-domain"
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
```

> Rebuild assets (`npm run build`) and `php artisan config:clear` after changing these, then restart the Reverb service.

### Caddy / FrankenPHP

Add inside your site block. Reverb's protocol uses the `/app/*` (websocket) and `/apps/*` (HTTP event API) paths:

```caddy
your-domain {
    # ... your existing PHP/static config ...

    @reverb path /app/* /apps/*
    reverse_proxy @reverb 127.0.0.1:8080
}
```

Caddy upgrades websocket connections automatically, so no extra header config is needed.

### nginx

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}

server {
    listen 443 ssl http2;
    server_name your-domain;

    # ... your existing ssl_certificate / PHP config ...

    location ~ ^/(app|apps)/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 3600s;   # keep long-lived sockets open
    }
}
```

### Verify

```bash
# Reverb up and listening locally
systemctl status laravel-reverb.service
ss -ltnp | grep 8080

# Handshake through the public domain (expects HTTP 101 Switching Protocols)
curl -i -N \
  -H "Connection: Upgrade" -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" -H "Sec-WebSocket-Key: x3JJHMbDL1EzLkh9GBhXDw==" \
  "https://your-domain/app/${REVERB_APP_KEY}"
```

In the browser, the Network → WS tab should show a `wss://your-domain/app/...` connection in the `101` state. If you instead see `ws://localhost:8080` (blocked) or a connection refused, the `VITE_REVERB_*` values weren't rebuilt or the proxy path isn't matching.
