<?php

declare(strict_types=1);

namespace AcMarche\News\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RolesEnum: string implements HasLabel
{
    case ROLE_NEWS_ADMIN = 'ROLE_NEWS_ADMIN';
    case ROLE_NEWS_VILLE = 'ROLE_NEWS_VILLE';
    case ROLE_NEWS_CPAS = 'ROLE_NEWS_CPAS';

    /**
     * @return array<string, string>
     */
    public static function getRoles(): array
    {
        $roles = [];
        foreach (self::cases() as $role) {
            $roles[$role->value] = $role->value;
        }

        return $roles;
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::ROLE_NEWS_ADMIN => 'Admin news',
            self::ROLE_NEWS_VILLE => 'News Ville',
            self::ROLE_NEWS_CPAS => 'News Cpas',
        };
    }
}
