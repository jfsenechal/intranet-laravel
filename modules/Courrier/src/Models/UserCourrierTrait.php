<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Models;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;

trait UserCourrierTrait
{
    /**
     * @return DepartmentCourrierEnum[]
     */
    public function getCourrierDepartments(): array
    {
        $departments = [];
        foreach (RolesEnum::getAdminRoles() as $role) {
            if ($this->hasRole($role->value)) {
                $departments[] = $role->getDepartment();
            }
        }

        return $departments;
    }
}
