<?php

declare(strict_types=1);

namespace AcMarche\Pst\Models\Scopes;

use AcMarche\Security\Repository\UserRepository;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasDepartmentScope
{
    #[Scope]
    protected function forSelectedDepartment(Builder $query): void
    {
        $department = UserRepository::departmentSelected();
        $query->where('department', '=', $department);
    }

    #[Scope]
    protected function forDepartment(Builder $query, string $department): void
    {
        $query->where('department', '=', $department);
    }
}
