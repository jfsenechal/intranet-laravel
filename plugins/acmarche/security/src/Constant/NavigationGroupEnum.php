<?php
namespace AcMarche\Security\Constant;


use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum NavigationGroupEnum: string implements HasColor, HasLabel, HasDescription, HasIcon
{
    case SETTINGS = "SETTINGS";

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
            self::SETTINGS => 'Paramètres',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SETTINGS => 'danger',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::SETTINGS => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SETTINGS => 'tabler-settings',
        };
    }
}

