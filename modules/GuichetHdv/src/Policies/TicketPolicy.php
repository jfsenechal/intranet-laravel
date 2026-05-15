<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Policies;

use AcMarche\GuichetHdv\Enums\RolesEnum;
use AcMarche\GuichetHdv\Models\Ticket;
use App\Models\User;

final class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasAnyRole($user);
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $this->hasAnyRole($user);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyRole($user);
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $this->hasAdminRole($user) || $user->username === $ticket->user_add;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $this->hasAdminRole($user) || $user->username === $ticket->user_add;
    }

    public function restore(): bool
    {
        return false;
    }

    public function forceDelete(): bool
    {
        return false;
    }

    private function hasAnyRole(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasOneOfThisRoles([
            RolesEnum::ROLE_GUICHET_AGENT->value,
            RolesEnum::ROLE_GUICHET->value,
        ]);
    }

    private function hasAdminRole(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasOneOfThisRoles([RolesEnum::ROLE_GUICHET_AGENT->value]);
    }
}
