<?php

namespace AcMarche\Security\Constant;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RoleEnum: string implements HasColor, HasLabel, HasDescription, HasIcon
{
    case INTRANET_ADMIN = "ROLE_INTRANET_ADMIN";

    public static function toArray(): array
    {
        $values = [];
        foreach (self::cases() as $actionStateEnum) {
            $values[] = $actionStateEnum->value;
        }

        return $values;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::INTRANET_ADMIN => 'Administrateur',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INTRANET_ADMIN => 'success',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::INTRANET_ADMIN => 'Accès à tout et peut paramètrer l\'application',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::INTRANET_ADMIN => 'Administrateur',
        };
    }
}
