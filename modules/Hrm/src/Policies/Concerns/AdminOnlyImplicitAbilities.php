<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Policies\Concerns;

use App\Models\User;

/**
 * Deny the abilities Filament grants implicitly.
 *
 * `Filament\get_authorization_response()` returns `Response::allow()` when a
 * policy exists but declares no method for the ability being checked, so an
 * omitted `deleteAny()` or `replicate()` reads as *granted* — not denied —
 * even though `Gate::allows()` refuses it. Every table in this module exposes
 * `DeleteBulkAction`, and the contract tables expose
 * {@see \AcMarche\Hrm\Filament\Actions\ReplicateContractAction}, so each policy
 * reachable by a non-administrator must declare these explicitly.
 *
 * Requires {@see HrmAuthorization} for `isAdmin()`.
 */
trait AdminOnlyImplicitAbilities
{
    public function deleteAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDeleteAny(): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function replicate(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function reorder(User $user): bool
    {
        return $this->isAdmin($user);
    }
}
