<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Http\Controllers;

use AcMarche\MailingList\Models\EmailRecipient;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;

final class UnsubscribeController extends Controller
{
    /**
     * Show the unsubscribe confirmation page reached from the signed email link.
     */
    public function show(EmailRecipient $recipient): View
    {
        return view('mailing-list::unsubscribe.show', [
            'recipient' => $recipient,
            'confirmUrl' => URL::signedRoute('unsubscribe.store', ['recipient' => $recipient->getKey()]),
        ]);
    }

    /**
     * Record the unsubscribe request and display the confirmation.
     */
    public function store(EmailRecipient $recipient): View
    {
        $recipient->markAsUnsubscribed();

        return view('mailing-list::unsubscribe.confirmed', [
            'recipient' => $recipient,
        ]);
    }
}
