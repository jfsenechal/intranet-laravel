<?php

declare(strict_types=1);

namespace AcMarche\Pst\Models\Scopes;

use AcMarche\Security\Repository\UserRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class DepartmentScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $department = UserRepository::departmentSelected();
        $builder->where('department', $department);
    }
}
