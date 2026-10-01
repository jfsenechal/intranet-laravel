<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Training;
use AcMarche\Hrm\Policies\Concerns\AdminOnlyImplicitAbilities;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class TrainingPolicy
{
    use AdminOnlyImplicitAbilities;
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        // Filament gates every resource page on `viewAny`, so a direction head or a
        // CPAS/Ville reader needs it to reach the view page of a single record.
        // `scopeVisibleTo()` keeps the listing to the records they may open.
        return $this->hasAnyHrmRole($user);
    }

    /**
     * Mirrors {@see view()} at the query level so listings, counts and global
     * search never expose records the user cannot open.
     *
     * @param  Builder<Training>  $query
     * @return Builder<Training>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $this->scopeRecordsOfVisibleEmployees($query, $user);
    }

    public function view(User $user, Training $training): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $training->employee !== null
            && $this->canViewEmployee($user, $training->employee);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(): bool
    {
        return false;
    }
}
