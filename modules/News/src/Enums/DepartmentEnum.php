<?php

declare(strict_types=1);

namespace AcMarche\News\Enums;

use App\Models\User;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DepartmentEnum: string implements HasColor, HasIcon, HasLabel
{
    case COMMON = 'COMMON';
    case CPAS = 'CPAS';
    case VILLE = 'VILLE';

    public static function toArray(): array
    {
        $values = [];
        foreach (self::cases() as $actionStateEnum) {
            $values[] = $actionStateEnum->value;
        }

        return $values;
    }

    /**
     * The departments whose news the given user may read, following the rule
     * NewsNotification uses to pick recipients: COMMON news reaches everyone,
     * other news only the users of that department. Guests read COMMON and
     * VILLE news, administrators and news admins read everything.
     *
     * @return list<string>
     */
    public static function visibleTo(?User $user): array
    {
        if (! $user instanceof User) {
            return [self::COMMON->value, self::VILLE->value];
        }

        if ($user->isAdministrator() || $user->hasOneOfThisRoles([RolesEnum::ROLE_NEWS_ADMIN->value])) {
            return self::toArray();
        }

        return array_values(array_unique([self::COMMON->value, ...($user->departments ?? [])]));
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::COMMON => 'Cpas et Ville',
            self::CPAS => 'Cpas',
            self::VILLE => 'Ville',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::COMMON => 'warning',
            self::CPAS => 'primary',
            self::VILLE => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::COMMON => 'tabler-cell-signal-4',
            self::CPAS => 'tabler-cell-signal-2',
            self::VILLE => 'tabler-cell-signal-5',
        };
    }
}
