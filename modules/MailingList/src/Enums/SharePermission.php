<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SharePermission: string implements HasColor, HasLabel
{
    case Read = 'read';
    case Write = 'write';

    public function getLabel(): string
    {
        return match ($this) {
            self::Read => 'Lecture',
            self::Write => 'Modification',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Read => 'gray',
            self::Write => 'warning',
        };
    }
}
