<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Override;
use UnitEnum;

final class CitoyenPage extends Page
{
    #[Override]
    protected string $view = 'email-management::filament.pages.citoyen';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAtSymbol;

    #[Override]
    protected static ?int $navigationSort = 4;

    protected static string | UnitEnum | null $navigationGroup = 'Informations';

    #[Override]
    protected static ?string $navigationLabel = 'Informations';

    public function getTitle(): string
    {
        return 'Adresses mails citoyennes';
    }

    /**
     * @return array<int, array{command: string, description: string}>
     */
    public function getCommands(): array
    {
        return [
            [
                'command' => 'php artisan citoyen:update-last-login',
                'description' => 'Mets à jour la dernière date de connexion des mails basé sur les mails dans Maildir/new',
            ],
            [
                'command' => 'php artisan citoyen:orphan-mailboxes',
                'description' => 'Liste des dossiers IMAP sans compte utilisateur',
            ],
            [
                'command' => 'php artisan citoyen:orphan-mailboxes --delete',
                'description' => "Supprime les dossiers IMAP sans compte utilisateur, attention pas de confirmation !",
            ],
            [
                'command' => 'php artisan citoyen:unread-mail --only-with-mail --min-days=90',
                'description' => 'Vérifie si les comptes ont toujours des mails non-lus depuis 90 jours dans sont dossier Maildir/new',
            ],
            [
                'command' => 'php artisan citoyen:unread-mail --only-with-mail --min-days=365 --delete',
                'description' => 'Supprime les comptes dont le plus récent mail dans les non-lus est plus vieux que 365 jours',
            ],
        ];
    }
}
