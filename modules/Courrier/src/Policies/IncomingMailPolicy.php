<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Policies;

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use App\Models\User;

final class IncomingMailPolicy
{
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
     *
     * Restricted to users who administer or index at least one department.
     */
    public function viewAny(User $user): bool
    {
        return count($user->getCourrierViewableDepartments()) > 0;
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

        return $this->hasViewableDepartment($user, $incomingMail);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * The user must be an administrator and administer or index the mail's department.
     */
    public function update(User $user, IncomingMail $incomingMail): bool
    {
        return $this->isAdministrator($user) && $this->hasViewableDepartment($user, $incomingMail);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $this->isAdministrator($user);
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

    private function isAdministrator(User $user): bool
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

    /**
     * Check if the user is a recipient of the incoming mail.
     */
    private function isRecipientOfMail(User $user, IncomingMail $incomingMail): bool
    {
        return $incomingMail->recipients()
            ->where('recipients.username', $user->username)
            ->exists();
    }

    /**
     * Check if the user is a member of a service linked to the incoming mail.
     */
    private function isMemberOfLinkedService(User $user, IncomingMail $incomingMail): bool
    {
        $serviceIds = $incomingMail->services()->pluck('courrier_services.id');

        if ($serviceIds->isEmpty()) {
            return false;
        }

        return Recipient::query()
            ->where('recipients.username', $user->username)
            ->whereHas('services', fn ($query) => $query->whereIn('courrier_services.id', $serviceIds))
            ->exists();
    }
}
