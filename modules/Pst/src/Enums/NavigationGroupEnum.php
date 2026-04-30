<?php

declare(strict_types=1);

namespace AcMarche\Pst\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum NavigationGroupEnum implements HasColor, HasIcon, HasLabel
{
    // the order in which the cases are defined controls the order of the groups
    case Settings;
    case ProjectManagment;
    case Organisation;

    public static function toArray(): array
    {
        $values = [];
        foreach (ActionStateEnum::cases() as $actionStateEnum) {
            $values[] = $actionStateEnum->value;
        }

        return $values;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Settings => 'Paramètres',
            self::ProjectManagment => 'Projet',
            self::Organisation => 'Organisation',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Settings => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Settings => 'tabler-settings',
            self::ProjectManagment => 'tabler-project',
            self::Organisation => 'tabler-home',
        };
    }
}
