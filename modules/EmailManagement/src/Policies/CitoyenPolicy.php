<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Policies;

use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Policies\Concerns\EmailManagementAuthorization;
use App\Models\User;

/**
 * Email admins may read, create, correct and delete citoyens, one at a time: a deletion
 * also removes the Dovecot login and cannot be undone, so there is no bulk delete.
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
        return $this->isEmailAdmin($user);
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
