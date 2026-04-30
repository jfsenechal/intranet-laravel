<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Policies;

use AcMarche\Courrier\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class RegisterPolicies
{
    public static function register(): void
    {
        Gate::define('courrier-index', function (User $user): bool {
            if ($user?->isAdministrator()) {
                return true;
            }

            return $user->hasOneOfThisRoles([
                RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value,
                RolesEnum::ROLE_INDICATEUR_CPAS_INDEX->value,
                RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_INDEX->value,
            ]);
        });
        Gate::define('courrier-administrator', function (User $user): bool {
            if ($user?->isAdministrator()) {
                return true;
            }

            return $user->hasOneOfThisRoles([
                RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value,
                RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value,
                RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_ADMIN->value,
            ]);
        });
    }
}
