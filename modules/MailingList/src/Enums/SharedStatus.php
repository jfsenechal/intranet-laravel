<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum SharedStatus: string implements HasColor, HasIcon, HasLabel
{
    case Yes = 'yes';
    case No = 'no';

    public function getLabel(): string
    {
        return match ($this) {
            self::Yes => 'Oui',
            self::No => 'Non',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Yes => 'success',
            self::No => 'primary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Yes => Heroicon::CheckCircle->value,
            self::No => Heroicon::PaperAirplane->value,// heroicon-o-cog-6-tooth
        };
    }
}
