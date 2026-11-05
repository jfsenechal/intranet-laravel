<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Actions;

use AcMarche\EmailManagement\Models\Citoyen;
use Filament\Actions\DeleteAction;

final class DeleteCitoyenAction
{
    /**
     * The mailbox folder stays on the mail server, which this application cannot reach:
     * citoyen:orphan-mailboxes removes it there once the account is gone.
     */
    public static function make(): DeleteAction
    {
        return DeleteAction::make()
            ->authorize('delete')
            ->modalHeading(fn (Citoyen $record): string => "Supprimer {$record->mail}")
            ->modalDescription('Le compte et sa connexion à la messagerie seront supprimés définitivement. Le dossier des mails reste sur le serveur jusqu\'au passage de citoyen:orphan-mailboxes --delete.')
            ->using(function (Citoyen $record): bool {
                $record->deleteWithLogin();

                return true;
            });
    }
}
