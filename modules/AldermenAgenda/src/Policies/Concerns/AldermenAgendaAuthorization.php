<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Policies\Concerns;

use AcMarche\AldermenAgenda\Enums\RolesEnum;
use App\Models\User;

trait AldermenAgendaAuthorization
{
    protected function canAccess(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasRole(RolesEnum::ROLE_INVITATION->value);
    }
}
