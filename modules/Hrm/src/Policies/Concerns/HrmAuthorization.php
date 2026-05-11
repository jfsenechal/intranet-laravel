<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Policies\Concerns;

use AcMarche\Hrm\Enums\RolesEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Direction;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Employer;
use App\Models\User;

trait HrmAuthorization
{
    protected function isAdmin(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasRole(RolesEnum::ROLE_GRH_ADMIN->value);
    }

    protected function canReadCpas(User $user): bool
    {
        return $user->hasRole(RolesEnum::ROLE_GRH_CPAS_READ->value);
    }

    protected function canReadVille(User $user): bool
    {
        return $user->hasRole(RolesEnum::ROLE_GRH_VILLE_READ->value);
    }

    protected function isDirectionHead(User $user): bool
    {
        return $user->hasRole(RolesEnum::ROLE_GRH_DIRECTION->value);
    }

    protected function hasAnyHrmRole(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasOneOfThisRoles([
            RolesEnum::ROLE_GRH_ADMIN->value,
            RolesEnum::ROLE_GRH_CPAS_READ->value,
            RolesEnum::ROLE_GRH_VILLE_READ->value,
            RolesEnum::ROLE_GRH_DIRECTION->value,
        ]);
    }

    protected function canViewEmployee(User $user, Employee $employee): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($user->username === $employee->username) {
            return true;
        }

        $topSlugs = $this->employeeTopEmployerSlugs($employee);

        if ($this->canReadCpas($user) && in_array('cpas', $topSlugs, true)) {
            return true;
        }

        if ($this->canReadVille($user) && in_array('ville', $topSlugs, true)) {
            return true;
        }

        if ($this->isDirectionHead($user) && $this->employeeMatchesUserDirection($employee, $user)) {
            return true;
        }

        return false;
    }

    protected function canViewContract(User $user, Contract $contract): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($this->canReadCpas($user) || $this->canReadVille($user)) {
            $topSlug = $contract->employer ? $this->topEmployerSlug($contract->employer) : null;

            if ($this->canReadCpas($user) && $topSlug === 'cpas') {
                return true;
            }

            if ($this->canReadVille($user) && $topSlug === 'ville') {
                return true;
            }
        }

        if ($this->isDirectionHead($user)) {
            return in_array($contract->direction_id, $this->directionIdsForUser($user), true);
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function employeeTopEmployerSlugs(Employee $employee): array
    {
        return $employee->contracts()
            ->with('employer')
            ->get()
            ->map(fn (Contract $contract): ?Employer => $contract->employer)
            ->filter()
            ->map(fn (Employer $employer): ?string => $this->topEmployerSlug($employer))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function topEmployerSlug(Employer $employer): ?string
    {
        while ($employer->parent_id !== null && $employer->parent instanceof Employer) {
            $employer = $employer->parent;
        }

        return $employer->slug;
    }

    /**
     * @return array<int, int>
     */
    private function directionIdsForUser(User $user): array
    {
        if ($user->username === null) {
            return [];
        }

        return Direction::query()
            ->where('director', $user->username)
            ->pluck('id')
            ->all();
    }

    private function employeeMatchesUserDirection(Employee $employee, User $user): bool
    {
        $directionIds = $this->directionIdsForUser($user);

        if ($directionIds === []) {
            return false;
        }

        return $employee->contracts()
            ->active()
            ->whereIn('direction_id', $directionIds)
            ->exists();
    }
}
