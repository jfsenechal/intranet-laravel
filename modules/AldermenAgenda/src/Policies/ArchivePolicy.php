<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Policies;

use AcMarche\AldermenAgenda\Policies\Concerns\AldermenAgendaAuthorization;
use App\Models\User;

final class ArchivePolicy
{
    use AldermenAgendaAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function view(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function update(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function delete(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function restore(User $user): bool
    {
        return false;
    }

    public function forceDelete(): bool
    {
        return false;
    }
}
