<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Telework;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class TeleworkPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        // Filament gates every resource page on `viewAny`, so a director needs it to
        // reach the validation page. `scopeVisibleTo()` keeps the listing to their own
        // agents.
        return $this->isDirectionHead($user);
    }

    /**
     * Mirrors {@see view()} at the query level so listings and global search never
     * expose requests from agents outside the user's direction.
     *
     * @param  Builder<Telework>  $query
     * @return Builder<Telework>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($this->isAdmin($user)) {
            return $query;
        }

        /** @var array<int, callable(Builder<Telework>): Builder<Telework>> $conditions */
        $conditions = [];

        if ($user->username !== null) {
            $username = $user->username;
            $conditions[] = fn (Builder $query): Builder => $query->where('user_add', $username);
        }

        if ($this->isDirectionHead($user)) {
            $directionIds = $this->directionIdsForUser($user);

            if ($directionIds !== []) {
                $conditions[] = fn (Builder $query): Builder => $query->whereIn(
                    'user_add',
                    Employee::query()
                        ->select('username')
                        ->whereHas(
                            'contracts',
                            fn (Builder $query) => $query->active()->whereIn('direction_id', $directionIds),
                        ),
                );
            }
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

    public function view(User $user, Telework $telework): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($telework->user_add === $user->username) {
            return true;
        }

        return $this->validateAsManager($user, $telework);
    }

    /**
     * The director of the requester's direction decides on the request, so they may
     * reach the validation page without holding any GRH administration role.
     */
    public function validateAsManager(User $user, Telework $telework): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if (! $this->isDirectionHead($user)) {
            return false;
        }

        $employee = $telework->employee;

        return $employee instanceof Employee && $this->employeeMatchesUserDirection($employee, $user);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
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
