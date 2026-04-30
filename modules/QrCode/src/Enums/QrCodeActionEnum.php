<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Enums;

use Filament\Support\Contracts\HasLabel;

enum QrCodeActionEnum: string implements HasLabel
{
    case SMS = 'sms';
    case PHONE_NUMBER = 'phoneNumber';
    case EMAIL = 'email';
    case EPC = 'epc';
    case WIFI = 'wifi';
    case GEO = 'geo';
    case URL = 'url';
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
}
