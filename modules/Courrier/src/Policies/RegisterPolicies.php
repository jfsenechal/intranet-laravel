<?php

namespace AcMarche\Courrier\Policies;

use AcMarche\Courrier\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class RegisterPolicies
{
    public static function register(): void
    {
        Gate::define('courrier-index', function (User $user) {
            if ($user?->isAdministrator()) {
                return true;
            }

            return $user->hasOneOfThisRoles([
                RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value,
                RolesEnum::ROLE_INDICATEUR_CPAS_INDEX->value,
                RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_INDEX->value,
            ]);
        });
    }
}
