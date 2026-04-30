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
     * @return DepartmentCourrierEnum[]
     */
    public static function getCurrentUserDepartments(): array
    {
        $user = auth()->user();

        if ($user === null) {
            return [];
        }

        return $user->getCourrierDepartments();
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $departments = self::getCurrentUserDepartments();
        if (count($departments) > 0) {
            $values = array_map(fn (DepartmentCourrierEnum $d) => $d->value, $departments);
            $builder->whereIn('department', $values);
        }
    }
}
