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
    public static function bootHasDepartmentScope(): void
    {
        static::addGlobalScope('department', function (Builder $query): void {
            $departments = DepartmentScope::getCurrentUserDepartments();
            if (count($departments) > 0) {
                $values = array_map(fn (DepartmentCourrierEnum $d) => $d->value, $departments);
                $query->whereIn($query->getModel()->getTable().'.department', $values);
            }
        });

        static::creating(function (Model $model): void {
            if (empty($model->department)) {
                $departments = DepartmentScope::getCurrentUserDepartments();
                if (count($departments) === 1) {
                    $model->department = $departments[0]->value;
                }
            }
        });
    }
}
