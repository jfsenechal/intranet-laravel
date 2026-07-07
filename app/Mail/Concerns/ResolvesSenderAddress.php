<?php

declare(strict_types=1);

namespace App\Mail\Concerns;

use App\Models\User;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Auth;

trait ResolvesSenderAddress
{
    /**
     * Resolve the "from" address from the currently authenticated user.
     *
     * Falls back to the application defaults when there is no authenticated
     * user, e.g. when the email is sent from a console command or queue worker.
     */
    protected function senderAddress(): Address
    {
        $user = Auth::user();

        if ($user instanceof User) {
            return new Address($user->email, $user->fullNameAsString());
        }

        return new Address(config('mail.from.address'), (string) config('app.name'));
    }
}
