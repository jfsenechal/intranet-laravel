<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ServicesEnum: string implements HasColor, HasLabel
{
    case ETAT_CIVIL = 'État civil';
    case POPULATION = 'Population';
    case ETRANGERS = 'Étrangers';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::ETAT_CIVIL => '#eb13bb',
            self::POPULATION => '#23e61f',
            self::ETRANGERS => '#1d25f0',
        };
    }

    /**
     * Shades generated from the service hex color, consumable by Filament components.
     *
     * @return array<int, string>
     */
    public function getColor(): array
    {
        return Color::hex($this->color());
    }
}
