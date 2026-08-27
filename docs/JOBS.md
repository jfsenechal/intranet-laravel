# Email Queue & Throttling

How the mailing-list app queues newsletter emails and sends them at a controlled
rate ("X mails per X minutes").

## How Laravel queues work

The project uses `QUEUE_CONNECTION=database`, so the flow is:

1. A class implementing `ShouldQueue` (`App\Jobs\SendEmailJob`) is **serialized
   into the `jobs` database table** instead of running immediately.
2. A long-running worker process — `php artisan queue:work` — polls that table,
   pops jobs, and runs their `handle()` method.
3. Each job row has an `available_at` timestamp. The worker **ignores a job
   until `available_at` is in the past**. That column is the hook used for
   "X mails per X minutes."

You control the rate by setting **when** each job becomes available — no extra
infrastructure required.

## Implementation: staggered `delay()`

Each `SendEmailJob` is dispatched with an increasing delay. `SendEmailJob` uses
the `Queueable` trait, so it exposes `->delay()`. Emails are spread **evenly**
within the window (e.g. 50 per 5 min → one every 6 s) so the SMTP server never
receives a burst.

### Configuration — `config/mailing-list.php`

```php
'throttle' => [
    'per_window'     => (int) env('MAIL_THROTTLE_PER_WINDOW', 50),
    'window_minutes' => (int) env('MAIL_THROTTLE_WINDOW_MINUTES', 5),
],
```

Override per environment in `.env`:

```env
MAIL_THROTTLE_PER_WINDOW=50
MAIL_THROTTLE_WINDOW_MINUTES=5
```

Setting `MAIL_THROTTLE_WINDOW_MINUTES=0` disables throttling — all jobs are
dispatched for immediate pickup.

### Dispatch logic — `app/Handler/MailerHandler.php`

```php
$perWindow       = max(1, (int) config('mailing-list.throttle.per_window'));
$windowMinutes   = max(0, (int) config('mailing-list.throttle.window_minutes'));
$secondsPerEmail = $windowMinutes > 0 ? ($windowMinutes * 60) / $perWindow : 0;

$jobs = $pendingRecipients->values()->map(
    fn ($recipient, int $index): SendEmailJob => (new SendEmailJob($email, $recipient))
        ->delay(now()->addSeconds((int) round($index * $secondsPerEmail)))
)->all();
```

- `->values()` re-indexes the collection so `$index` is a clean `0, 1, 2…`
  counter.
- This works inside `Bus::batch()` — delayed jobs still belong to the batch, and
  the `->then()` callback fires only once the last one completes.
- It is deterministic, preserves order, and creates **no queue churn** (jobs sit
  in the table until their time comes).

The "Sending started" notification reports the active rate when throttling is on.

## Alternative: `RateLimited` job middleware

Better suited to a continuous, open-ended stream than to one finite newsletter
blast. Define a limiter in `AppServiceProvider::boot()`:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('emails', fn () => Limit::perMinute(50));
```

Then in `SendEmailJob`:

```php
use Illuminate\Queue\Middleware\RateLimited;

public function middleware(): array
{
    return [new RateLimited('emails')];
}

public $tries = 50; // RateLimited releases + retries the job; give it room
```

When the limit is hit, the job is released back to the queue with a delay and
retried later. **Caveats:** every release consumes an attempt (hence the high
`$tries`), and the worker constantly re-polls the queue. The `delay()` approach
avoids both — which is why it is used for this project.

## Running the worker

None of this runs without a live worker process:

```bash
php artisan queue:work --queue=default
```

In production this must be kept alive, otherwise jobs simply accumulate in the
`jobs` table.

### systemd service (Debian 13)

A ready-to-use unit file is version-controlled at
[`deploy/laravel-queue.service`](deploy/laravel-queue.service). It starts the
worker at boot and restarts it if it crashes.

Install it:

```bash
sudo cp deploy/laravel-queue.service /etc/systemd/system/laravel-queue.service
sudo systemctl daemon-reload
sudo systemctl enable --now laravel-queue.service
```

Manage it:

```bash
systemctl status laravel-queue          # is it running? recent logs
journalctl -u laravel-queue -f          # live tail of the worker output
sudo systemctl restart laravel-queue    # restart (run this after every deploy)
sudo systemctl is-enabled laravel-queue # confirm it starts on boot
```

Why the `ExecStart` flags:

| Flag | Reason |
|------|--------|
| `--max-time=3600` | Worker exits cleanly each hour; systemd restarts it — frees leaked memory. |
| `--tries=3` | A failing job retries 3× then lands in `failed_jobs` instead of looping forever. |
| `--sleep=3` | Polls MariaDB every 3 s when the queue is empty. |
| `Restart=always` | Worker comes back after a crash or a server reboot. |
| `After=mariadb.service` | The queue *is* the `jobs` table — don't start before the DB. |

**`User=`**: the unit runs as `jfsenechal` because that owns `storage/`. If the
web server runs as a different user, pick one user for both and make `storage/`
+ `bootstrap/cache` owned by it, or the worker hits permission errors.

### Restart the worker after every deploy

A queue worker is a long-lived PHP process — it loads your code once and keeps
it in memory. After changing any job/handler code (`MailerHandler.php`,
`SendEmailJob.php`, …), the running worker keeps using the **old** code until
restarted:

```bash
sudo systemctl restart laravel-queue
```

Add that line to your deploy script.

## Operational notes

- Run `php artisan config:clear` after changing `.env` if config is cached.
- A large list at a slow rate means many queued rows with a future
  `available_at` — expected, and fine for the `database` queue driver.
