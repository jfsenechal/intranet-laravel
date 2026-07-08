<?php

declare(strict_types=1);

namespace App\Mail\Concerns;

use App\Models\User;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Auth;

trait ResolvesSenderAddress
{
    /**
     * The resolved "from" address.
     *
     * Cached on the mailable instance so a queued mailable keeps the sender
     * that was resolved while handling the originating web request. Public so
     * it is serialized with the mailable when pushed onto the queue.
     */
    public ?Address $resolvedSender = null;

    /**
     * Resolve the "from" address from the currently authenticated user.
     *
     * Falls back to the application defaults when there is no authenticated
     * user, e.g. when the email is sent from a console command or queue worker.
     * Once resolved the value is cached so it is not recomputed on the queue
     * worker, where {@see Auth::user()} returns null.
     */
    protected function senderAddress(): Address
    {
        if ($this->resolvedSender instanceof Address) {
            return $this->resolvedSender;
        }

        $user = Auth::user();

        if ($user instanceof User) {
            return $this->resolvedSender = new Address($user->email, $user->fullNameAsString());
        }

        return $this->resolvedSender = new Address(config('mail.from.address'), (string) config('app.name'));
    }

    /**
     * Eagerly capture the sender address in the current (authenticated) context.
     *
     * Queued mailables must call this from their constructor so the acting
     * user's address is serialized with the job instead of being re-resolved
     * on the queue worker, where {@see Auth::user()} returns null.
     */
    protected function captureSenderAddress(): void
    {
        $this->senderAddress();
    }
}
