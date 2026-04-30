<?php

declare(strict_types=1);

namespace AcMarche\App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RssFeedEnum: string implements HasLabel
{
    case LE_SOIR = 'https://www.lesoir.be/rss/31874/cible_principale';
    case AVENIR_LUXEMBOURG = 'https://www.lavenir.net/rss.aspx?foto=1&intro=1&section=zipcode&zipcode=6900';
    case UVCW = 'https://www.uvcw.be/rss/fil-rss.xml';
    case DH_LUXEMBOURG = 'https://www.dhnet.be/rss/section/regions/luxembourg.xml';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::LE_SOIR => 'Le Soir',
            self::AVENIR_LUXEMBOURG => 'L\'Avenir Luxembourg',
            self::UVCW => 'UVCW',
            self::DH_LUXEMBOURG => 'DH Luxembourg',
        };
    }
}
