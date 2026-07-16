<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Policies\Concerns;

use AcMarche\EmailManagement\Enums\RolesEnum;
use App\Models\User;

trait EmailManagementAuthorization
{
    /**
     * The single gate for this module: the email-management panel, the employes
     * resource and the mailing lists resource are all restricted to this role.
     *
     * Super administrators pass, matching HrmAuthorization::isAdmin() and the rest
     * of the application.
     */
    protected function isEmailAdmin(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasRole(RolesEnum::ROLE_EMAIL_ADMIN->value);
    }
}
