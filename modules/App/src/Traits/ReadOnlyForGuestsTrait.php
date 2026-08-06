<?php

declare(strict_types=1);

namespace AcMarche\App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Denies every write ability to a guest on a Filament resource registered in a
 * panel that is open without authentication.
 *
 * Policies alone are not enough: `Filament\get_authorization_response()` falls
 * back to `Response::allow()` when the policy has no method for the checked
 * ability, and strict authorization is off. `deleteAny` was the live example —
 * absent from every policy, so the bulk delete action was allowed for anyone.
 * Requiring a signed in user here closes that whole class of gap, including for
 * abilities added later.
 *
 * Reads are deliberately left alone: `canViewAny()` and `canView()` stay with
 * the policies, which is where guest access is opted into.
 */
trait ReadOnlyForGuestsTrait
{
    public static function canCreate(): bool
    {
        return Auth::check() && parent::canCreate();
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::check() && parent::canEdit($record);
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::check() && parent::canDelete($record);
    }

    public static function canDeleteAny(): bool
    {
        return Auth::check() && parent::canDeleteAny();
    }

    public static function canForceDelete(Model $record): bool
    {
        return Auth::check() && parent::canForceDelete($record);
    }

    public static function canForceDeleteAny(): bool
    {
        return Auth::check() && parent::canForceDeleteAny();
    }

    public static function canReorder(): bool
    {
        return Auth::check() && parent::canReorder();
    }

    public static function canReplicate(Model $record): bool
    {
        return Auth::check() && parent::canReplicate($record);
    }

    public static function canRestore(Model $record): bool
    {
        return Auth::check() && parent::canRestore($record);
    }

    public static function canRestoreAny(): bool
    {
        return Auth::check() && parent::canRestoreAny();
    }
}
