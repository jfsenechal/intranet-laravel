<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Models\Concerns;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
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
            $department = static::getCurrentUserDepartment();
            if ($department !== null) {
                $query->where($query->getModel()->getTable().'.department', $department->value);
            }
        });

        static::creating(function (Model $model): void {
            if (empty($model->department)) {
                $department = static::getCurrentUserDepartment();
                if ($department !== null) {
                    $model->department = $department->value;
                }
            }
        });
    }

    public static function getCurrentUserDepartment(): ?DepartmentCourrierEnum
    {
        $user = auth()->user();

        if ($user === null) {
            return null;
        }

        return $user->courrierDepartment();
    }

    public function scopeForDepartment(Builder $query, DepartmentCourrierEnum $department): Builder
    {
        return $query->withoutGlobalScope('department')
            ->where($this->getTable().'.department', $department->value);
    }

    public function scopeAllDepartments(Builder $query): Builder
    {
        return $query->withoutGlobalScope('department');
    }
}
