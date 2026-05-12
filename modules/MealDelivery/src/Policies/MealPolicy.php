<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Policies;

use AcMarche\MealDelivery\Policies\Concerns\MealDeliveryAuthorization;
use App\Models\User;

final class MealPolicy
{
    use MealDeliveryAuthorization;

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
