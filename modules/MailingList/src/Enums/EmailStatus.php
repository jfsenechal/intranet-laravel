<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EmailStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Sending = 'sending';
    case Sent = 'sent';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::Sending => 'Envoi en cours',
            self::Sent => 'Envoyé',
            self::Failed => 'Echec de l\'envoi',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'primary',
            self::Sending => 'warning',
            self::Sent => 'success',
            self::Failed => 'danger',
        };
    }

    public function getIcon2(): ?string
    {
        return match ($this) {
            self::Draft => Heroicon::EnvelopeOpen->value,
            self::Sending => Heroicon::PaperAirplane->value,
            self::Sent => Heroicon::Envelope->value,
            self::Failed => Heroicon::BellAlert->value,
        };
    }
}
