<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages\Concerns;

use AcMarche\CpasLibrary\Mail\FicheMail;
use AcMarche\CpasLibrary\Models\Fiche;
use Filament\Notifications\Notification;

trait NotifiesLibraryUsers
{
    /**
     * Mail the saved fiche to the library users when the `notify_users` checkbox
     * is ticked. The field is not dehydrated, so it is read from the raw form state.
     */
    private function notifyLibraryUsers(): void
    {
        if (! ($this->data['notify_users'] ?? false)) {
            return;
        }

        /** @var Fiche $fiche */
        $fiche = $this->getRecord();
        $count = FicheMail::sendToLibraryUsers($fiche);

        Notification::make()
            ->success()
            ->title("Fiche envoyée à {$count} utilisateur(s)")
            ->send();
    }
}
