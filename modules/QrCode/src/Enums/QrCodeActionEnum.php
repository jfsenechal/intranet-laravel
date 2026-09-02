<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum QrCodeActionEnum: string implements HasDescription, HasIcon, HasLabel
{
    case URL = 'url';
    case SMS = 'sms';
    case PHONE_NUMBER = 'phoneNumber';
    case EMAIL = 'email';
    case EPC = 'epc';
    case WIFI = 'wifi';
    case GEO = 'geo';
    case TEXT = 'text';

    public function getLabel(): string
    {
        return match ($this) {
            self::SMS => 'Envoyer un sms',
            self::PHONE_NUMBER => 'Appeler le numéro de téléphone',
            self::EMAIL => 'Envoyer un email',
            self::WIFI => 'Configurer un code wifi',
            self::URL => 'Accéder  un site web (url)',
            self::TEXT => 'Générer un texte',
            self::GEO => 'Géolocaliser un lieu',
            self::EPC => 'Effectuer un virement bancaire',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::SMS => 'Ouvre un nouveau sms avec le destinataire et le message déjà remplis.',
            self::PHONE_NUMBER => 'Lance un appel vers le numéro encodé, sans devoir le taper.',
            self::EMAIL => 'Ouvre un nouvel email avec le destinataire, le sujet et le message déjà remplis.',
            self::WIFI => 'Connecte le visiteur au réseau wifi sans lui communiquer le mot de passe.',
            self::URL => 'Ouvre un site web dans le navigateur du visiteur.',
            self::TEXT => 'Affiche un texte libre sur le téléphone qui scanne le code.',
            self::GEO => 'Ouvre des coordonnées GPS dans l\'application de cartes du visiteur.',
            self::EPC => 'Prépare un virement SEPA avec le bénéficiaire, l\'IBAN et le montant.',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::SMS => 'heroicon-o-chat-bubble-left-right',
            self::PHONE_NUMBER => 'heroicon-o-phone',
            self::EMAIL => 'heroicon-o-envelope',
            self::WIFI => 'heroicon-o-wifi',
            self::URL => 'heroicon-o-globe-alt',
            self::TEXT => 'heroicon-o-document-text',
            self::GEO => 'heroicon-o-map-pin',
            self::EPC => 'heroicon-o-banknotes',
        };
    }
}
