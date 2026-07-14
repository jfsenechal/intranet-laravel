<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * Show the relation manager tab whenever the current user is allowed to view
 * the owner {@see \AcMarche\Hrm\Models\Employee}.
 *
 * The related models (Contract, Absence, Training, ...) keep an admin-only
 * `viewAny` policy so their standalone Filament resources stay restricted.
 * Relation managers only ever list an employee's own records, so gating the
 * tab on the employee `view` ability is both sufficient and correctly scoped
 * for direction heads and read-only roles.
 */
trait VisibleWhenEmployeeIsViewable
{
    #[Override]
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->can('view', $ownerRecord);
    }
}
