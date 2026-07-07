# Documentation

## Index

- [systemd services overview](#systemd-services-overview)
  - [Install the Laravel scheduler service](#install-the-laravel-scheduler-service)
  - [Install the Laravel queue worker service](#install-the-laravel-queue-worker-service)
  - [Install the Laravel Nightwatch agent service](#install-the-laravel-nightwatch-agent-service)
  - [Install the Laravel Reverb websocket service](#install-the-laravel-reverb-websocket-service)
- [Mail configuration (local vs production)](#mail-configuration-local-vs-production)
- [List systemd services](#list-systemd-services)

## systemd services overview

The app relies on several long-running `systemd` units (defined in `deploy/`). Each one runs a single Artisan process under the `frankenphp` user from `WorkingDirectory=/var/www/intranet`.

| Service | Unit file | Goal |
|---------|-----------|------|
| Scheduler | `deploy/laravel-schedule.service` | Runs `schedule:work` so scheduled/cron tasks (reminders, purges, indexing) fire at their defined times — replaces a system crontab entry. |
| Queue worker | `deploy/laravel-queue.service` | Runs `queue:work` to process queued jobs and mail (e.g. incoming-mail notifications) in the background; restarts hourly to release leaked memory. |
| Nightwatch agent | `deploy/laravel-nightwatch.service` | Runs `nightwatch:agent` to ship application telemetry (requests, queries, jobs, exceptions) to Laravel Nightwatch for monitoring. |
| Reverb websocket | `deploy/laravel-reverb.service` | Runs `reverb:start` as a websocket server relaying broadcast events (real-time ticket updates and notifications) to browsers. |

## Install the Laravel scheduler service

The unit file lives at `deploy/laravel-schedule.service`. It runs `php artisan schedule:work` as a long-running process — no system cron entry needed.

```bash
sudo cp deploy/laravel-schedule.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now laravel-schedule.service
```

Verify it came up:

```bash
systemctl status laravel-schedule.service
journalctl -u laravel-schedule.service -f
```

> Do **not** also add `* * * * * php artisan schedule:run` to crontab — with `schedule:work` running, scheduled tasks would fire twice.

## Install the Laravel queue worker service

The unit file lives at `deploy/laravel-queue.service`. It runs `php artisan queue:work` and restarts it hourly to free leaked memory.

```bash
sudo cp deploy/laravel-queue.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now laravel-queue.service
```

Verify it came up:

```bash
systemctl status laravel-queue.service
journalctl -u laravel-queue.service -f
```

After deploying new code, tell the worker to pick it up (the unit's `ExecStop` already calls this on stop, but you can trigger it any time):

```bash
php artisan queue:restart
```

## Install the Laravel Nightwatch agent service

The unit file lives at `deploy/laravel-nightwatch.service`. It runs `php artisan nightwatch:agent` as a long-running process that ships application telemetry (requests, queries, jobs, exceptions) to Laravel Nightwatch.

```bash
sudo cp deploy/laravel-nightwatch.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now laravel-nightwatch.service
```

Verify it came up:

```bash
systemctl status laravel-nightwatch.service
journalctl -u laravel-nightwatch.service -f
```

> The agent runs as the `frankenphp` user/group from `WorkingDirectory=/var/www/intranet`. Make sure `NIGHTWATCH_TOKEN` is set in the app's `.env` so the agent can authenticate.

## Install the Laravel Reverb websocket service

The unit file lives at `deploy/laravel-reverb.service`. It runs `php artisan reverb:start` as a long-running websocket server that relays broadcast events (real-time ticket updates and notifications) to the browsers.

```bash
sudo cp deploy/laravel-reverb.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now laravel-reverb.service
```

Verify it came up:

```bash
systemctl status laravel-reverb.service
journalctl -u laravel-reverb.service -f
```

After editing the unit file, reload and restart:

> The server binds `0.0.0.0:8080` and must match `REVERB_PORT` / `VITE_REVERB_PORT` in the app's `.env`. On HTTPS, browsers require `wss://`, so proxy `wss://your-domain/app/...` to `127.0.0.1:8080` and set `VITE_REVERB_SCHEME=https` (keep the internal `REVERB_SCHEME=http`). See `REVERB.md` for the full reverse-proxy setup. Restart this service after changing any `BROADCAST_*` or `REVERB_*` env values.

## Mail configuration (local vs production)

The mail transport is **not** branched by environment in code — it is driven entirely by the `MAIL_MAILER` env var. The project's `config/mail.php` only overrides `redirect_to`; the list of available mailers (`smtp`, `log`, `ses`, `sendmail`, `array`, …) comes from Laravel's framework-default `mail` config.

| Mode | File | `MAIL_MAILER` | Result |
|------|------|---------------|--------|
| Production / current `.env` | `.env` | `smtp` | Sent via `smtp.marche.be:465` (SSL), user `jfsmtp` |
| Local (template) | `.env.example` | `log` | Written to the log instead of being sent |

To use the log driver locally, set `MAIL_MAILER=log` in your local `.env` (as `.env.example` does); production uses `MAIL_MAILER=smtp`. The `smtp` mailer settings (`MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, …) all come from the `MAIL_*` vars in the loaded `.env`.

### Default sender (`from`)

- `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME` set the application default sender (`config('mail.from.address')` / `config('app.name')`).
- Mailables using the `App\Mail\Concerns\ResolvesSenderAddress` trait resolve the `from` from the **currently authenticated user** (web / Filament). When there is no authenticated user — e.g. mail dispatched from a **console command**, the scheduler, or a queue worker — they fall back to the application default `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME`.

### Recipient redirect (non-production safety net)

`app/Providers/AppServiceProvider.php` redirects **all** recipients to `MAIL_REDIRECT_TO` when the app is **not** in production:

```php
if (! app()->environment('production') && config('mail.redirect_to')) {
    Mail::alwaysTo(config('mail.redirect_to'));
}
```

This does not change the transport — it just prevents non-prod environments from emailing real users. It is inactive while `MAIL_REDIRECT_TO` is unset.

## List systemd services

```bash
systemctl list-units --type=service                 # active services only
systemctl list-units --type=service --all           # include inactive/failed
systemctl list-unit-files --type=service            # every installed unit + enabled state
```

### Filter

```bash
systemctl list-units --type=service --state=running       # only currently running
systemctl list-units --type=service --state=failed        # only failed
systemctl list-unit-files --type=service --state=enabled  # only enabled at boot
systemctl list-units --type=service 'laravel-*'           # glob match (our units)
```

### Inspect one

```bash
systemctl status laravel-schedule.service           # state + recent logs
systemctl is-active laravel-schedule.service        # active / inactive
systemctl is-enabled laravel-schedule.service       # enabled / disabled
systemctl cat laravel-schedule.service              # show the unit file as loaded
journalctl -u laravel-schedule.service -f           # follow live logs
```

### Tip

`systemctl` pages output through `less`. Add `--no-pager` for scripts, or `| cat` to dump everything:

```bash
systemctl list-units --type=service --no-pager
```
