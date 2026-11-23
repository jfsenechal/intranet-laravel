<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Models\Concerns;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Repository\DepartmentScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait HasDepartmentScope
{
    /**
     * Only the department administrators — the clerks who encode the mail —
     * are limited to their department, so their forms offer only the services
     * they route to. Everyone else sees every row: an index or read user who
     * belongs to another department's service must still be matched to it.
     */
    public static function bootHasDepartmentScope(): void
    {
        static::addGlobalScope('department', function (Builder $query): void {
            $department = DepartmentScope::getCurrentAdminUserDepartment();
            if ($department instanceof DepartmentCourrierEnum) {
                $query->where($query->getModel()->getTable().'.department', $department->value);
            }
        });

        static::creating(function (Model $model): void {
            if (empty($model->department)) {
                $department = DepartmentScope::getCurrentAdminUserDepartment();
                if ($department) {
                    $model->department = $department->value;
                }
            }
        });
    }
}
