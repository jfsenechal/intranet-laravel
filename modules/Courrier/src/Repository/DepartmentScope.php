<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class DepartmentScope implements Scope
{
    /**
     * Departments the current user administers. Used for the create auto-fill.
     *
     * @return DepartmentCourrierEnum|null
     */
    public static function getCurrentAdminUserDepartment(): ?DepartmentCourrierEnum
    {
        $user = auth()->user();

        if ($user === null) {
            return null;
        }

        return $user->getCourrierAdminDepartment();
    }

    /**
     * Departments the current user may assign when creating or editing mail.
     *
     * Department admins are limited to the departments they administer.
     *
     * @return DepartmentCourrierEnum[]
     */
    public static function getAssignableDepartments(): array
    {
        $user = auth()->user();

        if ($user === null) {
            return [];
        }

        return $user->getCourrierAdminDepartment();
    }

    /**
     * Departments the current user may see. Used to scope read queries.
     *
     * @return DepartmentCourrierEnum[]
     */
    public static function getViewableDepartments(): array
    {
        $user = auth()->user();

        if ($user === null) {
            return [];
        }

        return $user->getCourrierViewableDepartments();
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $departments = self::getViewableDepartments();
        if (count($departments) > 0) {
            $values = array_map(fn(DepartmentCourrierEnum $d) => $d->value, $departments);
            $builder->whereIn($model->getTable().'.department', $values);
        }
    }
}
