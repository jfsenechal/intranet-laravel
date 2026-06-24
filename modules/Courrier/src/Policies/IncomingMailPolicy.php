<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Policies;

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Policies\Concerns\ChecksMailAccess;
use App\Models\User;

final class IncomingMailPolicy
{
    use ChecksMailAccess;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user): ?bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view the listing.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * Recipients and linked-service members may view their mail. Users who
     * administer or index the mail's department may view it as well.
     */
    public function view(User $user, IncomingMail $incomingMail): bool
    {
        if ($this->isRecipientOfMail($user, $incomingMail)) {
            return true;
        }

        if ($this->isMemberOfLinkedService($user, $incomingMail)) {
            return true;
        }

        // for admin or index users
        return $this->hasViewableDepartment($user, $incomingMail);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isAdministratorIndicateur($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * The user must be an administrator and administer or index the mail's department.
     */
    public function update(User $user, IncomingMail $incomingMail): bool
    {
        return $this->isAdministratorIndicateur($user) && $this->hasViewableDepartment($user, $incomingMail);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, IncomingMail $incomingMail): bool
    {
        return $this->isAdministratorIndicateur($user) && $this->hasViewableDepartment($user, $incomingMail);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(): bool
    {
        return false;
    }

    private function isAdministratorIndicateur(User $user): bool
    {
        return $user->hasOneOfThisRoles(
            [
                RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value,
                RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value,
                RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_ADMIN->value,
            ]
        );
    }

    /**
     * Check if the user administers or indexes the mail's department.
     */
    private function hasViewableDepartment(User $user, IncomingMail $incomingMail): bool
    {
        if ($incomingMail->department === null) {
            return false;
        }

        foreach ($user->getCourrierViewableDepartments() as $department) {
            if ($department->value === $incomingMail->department) {
                return true;
            }
        }

        return false;
    }
}
