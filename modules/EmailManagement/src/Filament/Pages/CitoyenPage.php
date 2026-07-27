<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Override;

final class CitoyenPage extends Page
{
    #[Override]
    protected string $view = 'email-management::filament.pages.citoyen';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAtSymbol;

    #[Override]
    protected static ?int $navigationSort = 10;

    #[Override]
    protected static ?string $navigationLabel = 'Adresses mails citoyennes';

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
                'command' => 'php artisan citoyen:purge',
                'description' => 'Nettoyage des adresses mails inactives',
            ],
            [
                'command' => 'php artisan citoyen:change-password',
                'description' => "Changer le mot de passe d'un compte administrateur",
            ],
            [
                'command' => 'php artisan citoyen:new-mail --only-with-mail',
                'description' => 'Vérifie si le compte a des mails dans sont dossier Maildir/new',
            ],
            [
                'command' => 'php artisan citoyen:send-message',
                'description' => 'Envoi d\'un message à tous les citoyens. Le texte est dans resources/views/mail',
            ],
        ];
    }
}
