<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Policies;

use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Policies\Concerns\EmailManagementAuthorization;
use App\Models\User;

/**
 * Email admins may read, create and correct citoyens. Citoyens are removed by the citizen
 * application, never from this panel.
 */
final class CitoyenPolicy
{
    use EmailManagementAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->isEmailAdmin($user);
    }

    public function view(User $user, Citoyen $citoyen): bool
    {
        return $this->isEmailAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isEmailAdmin($user);
    }

    public function update(User $user, Citoyen $citoyen): bool
    {
        return $this->isEmailAdmin($user);
    }

    public function delete(User $user, Citoyen $citoyen): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, Citoyen $citoyen): bool
    {
        return false;
    }

    public function forceDelete(User $user, Citoyen $citoyen): bool
    {
        return false;
    }
}
