# Mail & Queue

How email is configured, sent, deferred, and monitored in this application.

## Index

- [1. Configuration (local vs production)](#1-configuration-local-vs-production)
  - [Default sender (`from`)](#default-sender-from)
  - [Recipient redirect (non-production safety net)](#recipient-redirect-non-production-safety-net)
- [2. Delivery methods explained (sync, queue, jobs)](#2-delivery-methods-explained-sync-queue-jobs)
  - [The two building blocks](#the-two-building-blocks)
  - [The five ways mail leaves this app](#the-five-ways-mail-leaves-this-app)
  - [Queue vs Job](#queue-vs-job)
  - [Should you queue everywhere?](#should-you-queue-everywhere)
- [3. Sending inventory (modules & app)](#3-sending-inventory-modules--app)
- [4. Queue infrastructure](#4-queue-infrastructure)
- [5. Queue monitoring UI](#5-queue-monitoring-ui)
- [6. Other useful notes](#6-other-useful-notes)

---

## 1. Configuration (local vs production)

The mail transport is **not** branched by environment in code — it is driven entirely by the `MAIL_MAILER` env var. The project's `config/mail.php` only overrides `redirect_to`; the list of available mailers (`smtp`, `log`, `ses`, `sendmail`, `array`, …) comes from Laravel's framework-default `mail` config.

| Mode | File | `MAIL_MAILER` | Result |
|------|------|---------------|--------|
| Production / current `.env` | `.env` | `smtp` | Sent via `smtp.marche.be:465` (SSL), user `jfsmtp` |
| Local (template) | `.env.example` | `log` | Written to the log instead of being sent |

To use the log driver locally, set `MAIL_MAILER=log` in your local `.env` (as `.env.example` does); production uses `MAIL_MAILER=smtp`. The `smtp` mailer settings (`MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, …) all come from the `MAIL_*` vars in the loaded `.env`.

### Default sender (`from`)

- `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME` set the application default sender (`config('mail.from.address')` / `config('app.name')`).
- Mailables using the `App\Mail\Concerns\ResolvesSenderAddress` trait resolve the `from` from the **currently authenticated user** (web / Filament). When there is no authenticated user — e.g. mail dispatched from a **console command**, the scheduler, or a queue worker — they fall back to the application default `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME`.
- **Queue-safety:** the trait caches the resolved `Address`, and every *queued* mailable that uses it calls `captureSenderAddress()` in its constructor. This resolves the acting user **in the web request** and serializes the address into the job, so the correct `from` survives to the worker — where `Auth::user()` is `null`. See [Queue infrastructure](#4-queue-infrastructure).

### Recipient redirect (non-production safety net)

`app/Providers/AppServiceProvider.php` redirects **all** recipients to `MAIL_REDIRECT_TO` when the app is **not** in production:

```php
if (! app()->environment('production') && config('mail.redirect_to')) {
    Mail::alwaysTo(config('mail.redirect_to'));
}
```

This does not change the transport — it just prevents non-prod environments from emailing real users. It is inactive while `MAIL_REDIRECT_TO` is unset.

---

## 2. Delivery methods explained (sync, queue, jobs)

There are really **two axes**: *when* the send happens (synchronous vs. deferred) and *what mechanism* defers it (a `ShouldQueue` mailable, an explicit `->queue()`, or a dedicated `Job`). They all end up on the same `database` queue.

### The two building blocks

**The transport** — `MAIL_MAILER=smtp` (`smtp.marche.be:465`). This is *how* an email physically leaves, and it is the same regardless of delivery method. It is also the slow part (a network round-trip to the SMTP server), which is the whole reason queuing matters.

**The queue** — `QUEUE_CONNECTION=database`. "Deferring" work means serializing it as a row in the `jobs` table and returning immediately. A separate long-running process — the **worker** (`php artisan queue:work`, run as a systemd service) — later pulls those rows and executes them. If a job throws, it moves to `failed_jobs`.

So: **sync** = do it now, in this process, and make the caller wait. **Queued** = write a row and let the worker do it later.

### The five ways mail leaves this app

**1. Synchronous send — `->send()` on a plain Mailable**

```php
Mail::to($recipient)->send(new ReminderMail($fiches)); // ReminderMail is NOT ShouldQueue
```

The framework talks to SMTP **right here**, blocking the caller until the server responds. If SMTP is slow or down, the caller hangs or throws. Fine for **console commands** (nobody is waiting on an HTTP response); bad for Filament actions.

**2. Queued Mailable — `ShouldQueue` (the main pattern)**

```php
final class NewsEmail extends Mailable implements ShouldQueue { /* ... */ }

Mail::to($user->email)->send(new NewsEmail($news)); // same call — but now it queues
```

The **call site does not change**. Because the mailable implements `ShouldQueue`, `->send()` transparently serializes it into `jobs` and returns instantly; the worker rebuilds the mailable and does the real send. `SerializesModels` stores only each model's ID and re-fetches it fresh on the worker (so the model must still exist then). Sender resolution needs `captureSenderAddress()` — see [Default sender](#default-sender-from).

**3. Explicit `->queue()`**

```php
Mail::to($sender)->queue(new EventEmail($record));
```

Same outcome as #2 (a row in `jobs`), but the decision is made **at the call site** instead of on the class. Used where the *same* mailable is sometimes sync and sometimes queued — e.g. `AldermenAgenda\EventEmail` is `->send()` for the instant preview but `->queue()` for the real send.

**4. Sending from inside a Job — a `ShouldQueue` Job class**

```php
dispatch(new SendIncomingMailNotificationJob($date)); // the Job is ShouldQueue
// ...later, on the worker, inside the job's handle():
Mail::to($recipient->email)->send(new IncomingMailNotification(/* ... */));
```

Here the *unit of work* is queued, not the mailable. You dispatch a **Job** you wrote, and the send happens **inside** it on the worker. Use this over #2 when there is **logic around the send** — building the recipient list, looping, deciding attachments — that you also want off the request.

**5. Batched jobs — `Bus::batch`**

```php
Bus::batch($jobs)               // one SendEmailJob per recipient
    ->then(fn () => $email->update(['status' => EmailStatus::Sent]))
    ->catch(fn () => $email->update(['status' => EmailStatus::Failed]))
    ->dispatch();
```

`MailingList` fans a newsletter into **many** `SendEmailJob`s tracked as a group in the `job_batches` table, each with a staggered `->delay()` to throttle the blast, plus `then` / `catch` callbacks that update the campaign status. This is #4 scaled to bulk with progress tracking.

### Queue vs Job

"Queue or job?" is like "road or car?" — they are different layers of the same thing.

- **The queue** is the *channel*: the `jobs` table + the connection (`database`) + the worker that drains it. Infrastructure — a waiting line.
- **A job** is *one unit of work* placed in that line — a serialized "do this later" instruction. The worker pulls a job off the queue and runs it.

Everything deferred becomes a job on the queue. The practical distinction is **who writes the job class**:

| | Queued mailable (`ShouldQueue`) | Custom Job (`ShouldQueue`) |
|---|---|---|
| What is queued | one email | any block of work |
| Job class | Laravel's, automatic (`Illuminate\Mail\SendQueuedMailable` — the `displayName` in `/admin/jobs`) | yours, explicit — you own `handle()` |
| Good for | "send *this* email later" | "figure out what to do, then do it, later" |
| In this app | News, Hrm telework, Conseil, Agent, Pst reminder | Courrier notification, MailingList `SendEmailJob` |

**Rule of thumb:** one email → make the **mailable** `ShouldQueue`. Logic-around-the-send (build the list, loop, decide attachments) → write a **Job**. A Job can itself queue mailables (that is exactly what `SendEmailJob` does).

### Should you queue everywhere?

No. Queuing is the right default **for user-facing actions that do slow or failure-prone I/O** (SMTP, external APIs, PDF/image processing). It is not free, and in several places it is pointless or worse.

**Keep it sync when:**

- **Console commands / scheduled tasks** — already off the request; nobody waits on an HTTP response. Queuing just adds a hop and hides failures from the command's own exit code/logs. (CpasLibrary / Hrm reminders.)
- **The result is needed before the response is meaningful** — a preview, a synchronously-generated download, a value you then render. (`EventEmail` preview stays `->send()`.)
- **The operation is tiny, rare, and local** — not worth a queue round-trip.

**What queuing costs you:**

1. **Hidden failures.** A sync send throws in the request where the user sees it; a queued send fails silently into `failed_jobs`. This is why the [monitoring UI](#5-queue-monitoring-ui) exists.
2. **An operational dependency.** No running worker → queued mail never leaves; it accumulates in `jobs`.
3. **Serialization constraints.** Payloads must serialize; models go by ID and are re-fetched later; request-scoped state such as `Auth::user()` is gone on the worker (the reason for `captureSenderAddress()`).
4. **Eventual, not immediate.** There is a delay, and jobs can run out of order or retry — wrong for anything the next line of code depends on.
5. **Harder to debug.** Stack traces split across request and worker.

**The heuristic:**

> Queue when the work is **slow or flaky, the caller is a human waiting on a web/Filament response, and the result can happen a few seconds later**. Keep it sync when it is **fast, already off the request (console/cron), or its result is needed right now**.

**Decision table:**

| You have… | Use | Example here |
|---|---|---|
| A console command | Plain sync `->send()` | CpasLibrary reminders |
| A Filament action sending one email | `ShouldQueue` mailable (#2) | Hrm telework, News |
| One mailable, sometimes preview / sometimes real | `->send()` vs `->queue()` (#3) | AldermenAgenda `EventEmail` |
| Recipient-resolution or heavy prep before sending | A `ShouldQueue` **Job** (#4) | Courrier notification |
| A bulk blast needing throttle + status | `Bus::batch` (#5) | MailingList newsletter |

---

## 3. Sending inventory (modules & app)

All outgoing business mail is sent **from the modules**, never from `app/`. Every Mailable lives under `modules/*/src/Mail/` and is sent through `Illuminate\Support\Facades\Mail`. The `app/` layer only holds the shared sender trait (`App\Mail\Concerns\ResolvesSenderAddress`) and the `Mail::alwaysTo()` redirect (see [Configuration](#1-configuration-local-vs-production)); it dispatches no business email of its own.

A mailable is delivered **asynchronously** when it implements `ShouldQueue`, is sent with `->queue(...)`, or is sent from inside a queued `Job`; otherwise `Mail::to()->send(...)` blocks the current request/command until the SMTP server responds.

| Module | Mailable | Triggered from | Delivery |
|--------|----------|----------------|----------|
| Conseil | `ConseilNotificationMail` | `NotifyRecipients` Filament page | Queue — `ShouldQueue` |
| Pst | `ActionReminderMail` | `ReminderAction` (Filament action) | Queue — `ShouldQueue` |
| Agent | `ProfileRequestMail` | Hrm `RequestProfileAction` | Queue — `ShouldQueue` |
| Agent | `ProfileChangeRequestMail` | Hrm `RequestProfileChangeAction` | Queue — `ShouldQueue` |
| Agent | `ProfileDeleteRequestMail` | Hrm `RequestProfileDeletionAction` | Queue — `ShouldQueue` |
| Hrm | `TeleworkManagerValidationMail` | `TeleworkNotifier` (Telework pages) | Queue — `ShouldQueue` |
| Hrm | `TeleworkEmployeeManagerResultMail` | `TeleworkNotifier` | Queue — `ShouldQueue` |
| Hrm | `TeleworkHrValidationMail` | `TeleworkNotifier` | Queue — `ShouldQueue` |
| Hrm | `TeleworkEmployeeHrResultMail` | `TeleworkNotifier` | Queue — `ShouldQueue` |
| College | `NotificationMail` | `CreateNotification` page | Queue — `->queue()` |
| AldermenAgenda | `EventEmail` | `ViewEvent` page | Queue (`->queue()`) for the real send; **Sync** for the preview (`->send(..., isPreview: true)`) |
| Courrier | `IncomingMailNotification` | `SendIncomingMailNotificationJob` (dispatched from `NotifyRecipients` page) | Queue — via `ShouldQueue` job |
| MailingList | `NewsletterMail` | `SendEmailJob` batch (`MailerHandler`) | Queue — via batched `ShouldQueue` job; **Sync** for the preview (`PreviewAction`) |
| News | `NewsEmail` | `NewsNotification` event listener | Queue — `ShouldQueue` |
| Ad | `ClassifiedAdEmail` | `ClassifiedAdObserver` (model saved) | **Sync** — runs inside the web request |
| Pst | `ActionNewMail` | `SendActionNewNotification` listener | **Sync** |
| Pst | `ExceptionMail` | `Pst\Exceptions\Handler` | **Sync** |
| CpasLibrary | `ReminderMail` | `ReminderCommand` (console) | **Sync** (console — fine) |
| CpasLibrary | `ResumeMail` | `ResumeCommand` (console) | **Sync** (console — fine) |
| Hrm | `ReminderMail` | `ReminderCommand` (console) | **Sync** (console — fine) |
| Hrm | `PurgedApplicationsMail` | `PurgeCommand` (console) | **Sync** (console — fine) |
| EmailManagement | `CitoyenMessage` | — | **Unused** — no send site (dead code) |
| Pst | `ContactMessage` | — | **Unused** — no send site (dead code) |

> Sync sends triggered from **console commands** are intentional (a worker adds no value there). Sync sends still triggered from a listener/observer (Ad, Pst) block the originating request and are candidates for future queuing.

---

## 4. Queue infrastructure

- `QUEUE_CONNECTION=database`. Tables `jobs`, `failed_jobs`, `job_batches` live on the default (intranet) connection.
- **A worker must be running** for queued mail to leave — `php artisan queue:work` (see [Install the Laravel queue worker service](DOC.md#install-the-laravel-queue-worker-service) in `DOC.md`). Without it, queued mail simply accumulates in `jobs`.
- Queued failures land in `failed_jobs` and are **not** surfaced in the UI (unlike a sync send, which throws in the request). Retry them from the [monitoring UI](#5-queue-monitoring-ui) or with `php artisan queue:retry`.
- Non-mail `ShouldQueue` jobs in the app: `Courrier\SendIncomingMailNotificationJob`, `Courrier\IndexIncomingMailJob` (Meilisearch indexing), `MailingList\SendEmailJob`.
- **MailingList** uses `Bus::batch()` (the `job_batches` table) with a per-email delay to throttle the newsletter blast, and `then` / `catch` callbacks that update the campaign status (`EmailStatus::Sent` / `Failed`).
- **Sender preservation:** `ResolvesSenderAddress` caches the resolved `Address`; every queued mailable that uses it calls `captureSenderAddress()` in its constructor so the acting user's `from` is serialized into the job and survives to the worker (where there is no authenticated user). See [Default sender](#default-sender-from).
- After deploying new code, restart the worker so it picks up the new classes: `php artisan queue:restart`.

---

## 5. Queue monitoring UI

Read-only queue views in the **admin panel** (`/admin`), restricted to administrators (`User::isAdministrator()`):

- `/admin/jobs` — pending / reserved jobs (`App\Filament\Resources\Jobs\JobResource`), with payload viewer and delete.
- `/admin/failed-jobs` — failed jobs (`App\Filament\Resources\FailedJobs\FailedJobResource`), with exception viewer, retry, delete, and bulk retry / flush.
- Dashboard widget `App\Filament\Widgets\QueueStatsWidget` — pending and failed counts (each links to its resource).

These read the queue tables through the lightweight `App\Models\Job` and `App\Models\FailedJob` models.

---

## 6. Other useful notes

- `app/` sends only Filament **database notifications** (`AdminPanelProvider::databaseNotifications()`, `User` is `Notifiable`) and **web-push** subscriptions (`HasPushSubscriptions`) — not email.
- `GuichetHdv\TicketAssignedPush` is a **WebPush** notification, not an email.
- SMTP credentials live in `.env` (`MAIL_PASSWORD` in plaintext) — rotate / secret-store candidates.
