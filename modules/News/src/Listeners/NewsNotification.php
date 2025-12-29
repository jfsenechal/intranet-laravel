<?php

namespace AcMarche\News\Listeners;

use AcMarche\News\Events\NewsProcessed;
use AcMarche\News\Mail\NewsEmail;
use AcMarche\News\Models\News;
use Exception;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Address;

final class NewsNotification
{
    public function handle(NewsProcessed $event): void
    {
        // $this->sendMail($event->news());
    }

    private function sendMail(News $news): void
    {
        try {
            Mail::to(new Address('jf@marche.be'))
                ->send(new NewsEmail($news));
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
