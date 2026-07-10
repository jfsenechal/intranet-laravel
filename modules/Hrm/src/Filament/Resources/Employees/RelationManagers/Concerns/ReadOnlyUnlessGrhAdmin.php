<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns;

use AcMarche\Hrm\Enums\RolesEnum;
use App\Models\User;

trait ReadOnlyUnlessGrhAdmin
{
    /**
     * Relation managers are read-only for everyone except administrators and
     * users holding the {@see RolesEnum::ROLE_GRH_ADMIN} role, who keep the
     * create, edit and delete actions.
     */
    public function isReadOnly(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return true;
        }

        if ($user->isAdministrator()) {
            return false;
        }

        return ! $user->hasRole(RolesEnum::ROLE_GRH_ADMIN->value);
    }
}
