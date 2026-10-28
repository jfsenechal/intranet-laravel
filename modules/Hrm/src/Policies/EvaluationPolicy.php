<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Evaluation;
use AcMarche\Hrm\Policies\Concerns\AdminOnlyImplicitAbilities;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class EvaluationPolicy
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
        // `scopeVisibleTo()` keeps the resource query to the records they may open.
        return $this->hasAnyHrmRole($user);
    }

    /**
     * Mirrors {@see view()} at the query level so listings, counts and global
     * search never expose records the user cannot open.
     *
     * @param  Builder<Evaluation>  $query
     * @return Builder<Evaluation>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $this->scopeRecordsOfVisibleEmployees($query, $user);
    }

    public function view(User $user, Evaluation $evaluation): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $evaluation->employee !== null
            && $this->canViewEmployee($user, $evaluation->employee);
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
