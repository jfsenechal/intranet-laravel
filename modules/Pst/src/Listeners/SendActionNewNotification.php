<?php

declare(strict_types=1);

namespace AcMarche\Pst\Listeners;

use AcMarche\Pst\Events\ActionProcessed;
use AcMarche\Pst\Mail\ActionNewMail;
use AcMarche\Pst\Models\Action;
use Exception;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Address;

final class SendActionNewNotification
{
    public function handle(ActionProcessed $event): void
    {
        $action = $event->action();
        $this->sendMail($action);
    }

    private function sendMail(Action $action): void
    {
        try {
            Mail::to(new Address(config('pst.validator.email')))
                ->send(new ActionNewMail($action));
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
