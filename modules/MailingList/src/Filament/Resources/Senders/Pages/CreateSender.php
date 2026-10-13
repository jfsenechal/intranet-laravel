<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Senders\Pages;

use AcMarche\MailingList\Filament\Resources\Senders\SenderResource;
use AcMarche\MailingList\Models\Sender;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateSender extends CreateRecord
{
    #[Override]
    protected static string $resource = SenderResource::class;

    #[Override]
    protected static ?string $title = 'Nouvel expéditeur';

    /**
     * Prefill the form with the current user's identity when they create their first sender.
     */
    protected function afterFill(): void
    {
        if (Sender::query()->exists()) {
            return;
        }

        $user = auth()->user();

        $this->data['name'] = mb_trim($user->first_name.' '.$user->last_name);
        $this->data['email'] = $user->email;
    }
}
