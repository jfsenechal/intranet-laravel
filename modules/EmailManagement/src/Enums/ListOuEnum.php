<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

/**
 * The two Active Directory organisational units holding mail groups.
 *
 * Each case names a key under email-management.ldap.bases, so the base DN stays in config
 * rather than being spelled out here.
 */
enum ListOuEnum: string implements HasLabel
{
    case LISTS = 'lists';
    case SERVICES = 'services';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::LISTS => 'Listes de diffusion',
            self::SERVICES => 'Services',
        };
    }

    public function baseDn(): ?string
    {
        return config('email-management.ldap.bases.'.$this->value);
    }
}
