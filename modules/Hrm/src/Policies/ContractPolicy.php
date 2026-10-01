<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class ContractPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        // Filament gates every resource page on `viewAny`, so a direction head or a
        // CPAS/Ville reader needs it to reach the view page of a single contract.
        // `scopeVisibleTo()` keeps the listing to the contracts they may open.
        return $this->hasAnyHrmRole($user);
    }

    /**
     * Mirrors {@see view()} at the query level so listings, counts and global
     * search never expose contracts the user cannot open.
     *
     * @param  Builder<Contract>  $query
     * @return Builder<Contract>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($this->isAdmin($user)) {
            return $query;
        }

        /** @var array<int, callable(Builder<Contract>): Builder<Contract>> $conditions */
        $conditions = [];

        foreach (['cpas' => $this->canReadCpas($user), 'ville' => $this->canReadVille($user)] as $slug => $isGranted) {
            if (! $isGranted) {
                continue;
            }

            $employerIds = $this->employerIdsForTopSlug($slug);

            if ($employerIds !== []) {
                $conditions[] = fn (Builder $query): Builder => $query->whereIn('employer_id', $employerIds);
            }
        }

        if ($this->isDirectionHead($user)) {
            $directionIds = $this->directionIdsForUser($user);

            if ($directionIds !== []) {
                $conditions[] = fn (Builder $query): Builder => $query->whereIn('direction_id', $directionIds);
            }

            $conditions[] = fn (Builder $query): Builder => $query->whereHas(
                'employee',
                fn (Builder $query): Builder => $this->scopeVisibleEmployees($query, $user),
            );
        }

        if ($conditions === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $query) use ($conditions): void {
            foreach ($conditions as $condition) {
                $query->orWhere($condition);
            }
        });
    }

    public function view(User $user, Contract $contract): bool
    {
        return $this->canViewContract($user, $contract);
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
