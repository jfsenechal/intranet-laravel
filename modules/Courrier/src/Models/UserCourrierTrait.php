<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Models;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;

trait UserCourrierTrait
{
    /**
     * Departments the user administers (manage, create, download attachments).
     *
     * @return DepartmentCourrierEnum[]
     */
    public function getCourrierDepartments(): array
    {
        $departments = [];
        foreach (RolesEnum::getAdminRoles() as $role) {
            if ($this->hasRole($role->value)) {
                $departments[] = $role->getDepartmentAdmin();
            }
        }

        return $departments;
    }

    /**
     * Departments the user may index/read but not administer.
     *
     * @return DepartmentCourrierEnum[]
     */
    public function getCourrierIndexDepartments(): array
    {
        $departments = [];
        foreach (RolesEnum::getIndexRoles() as $role) {
            if ($this->hasRole($role->value)) {
                $departments[] = $role->getDepartmentIndex();
            }
        }

        return $departments;
    }

    /**
     * Every department the user may see (admin and index combined, de-duplicated).
     *
     * @return DepartmentCourrierEnum[]
     */
    public function getCourrierViewableDepartments(): array
    {
        $departments = [];
        foreach ([...$this->getCourrierDepartments(), ...$this->getCourrierIndexDepartments()] as $department) {
            $departments[$department->value] = $department;
        }

        return array_values($departments);
    }
}
