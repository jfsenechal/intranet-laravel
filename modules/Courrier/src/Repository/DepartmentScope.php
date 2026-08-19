<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class DepartmentScope implements Scope
{
    /**
     * Department the current user administers. Used for the create auto-fill.
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
     * Department the current user may assign when creating or editing mail.
     */
    public static function getAssignableDepartment(): ?DepartmentCourrierEnum
    {
        $user = auth()->user();

        if ($user === null) {
            return null;
        }

        return $user->getCourrierAdminDepartment();
    }

    /**
     * Whether the current user administers the CPAS department. Only they
     * classify their mail, so the category field, filter and column are theirs.
     *
     * Asks for the role rather than going through getAssignableDepartment(),
     * which reduces a user to a single admin department.
     */
    public static function currentUserAdministersCpas(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->hasRole(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value);
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
            $values = array_map(fn (DepartmentCourrierEnum $d) => $d->value, $departments);
            $builder->whereIn($model->getTable().'.department', $values);
        }
    }
}
