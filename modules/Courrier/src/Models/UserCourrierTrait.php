<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Models;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;

trait UserCourrierTrait
{
    /**
     * Department the user administers (manage, create, download attachments).
     * A user holds at most one admin role.
     */
    public function getCourrierAdminDepartment(): ?DepartmentCourrierEnum
    {
        foreach (RolesEnum::getAdminRoles() as $role) {
            if ($this->hasRole($role->value)) {
                return $role->getDepartmentAdmin();
            }
        }

        return null;
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
     * Every department the user may see (admin and index combined, deduplicated).
     *
     * @return DepartmentCourrierEnum[]
     */
    public function getCourrierViewableDepartments(): array
    {
        $departments = [];

        $adminDepartment = $this->getCourrierAdminDepartment();
        if ($adminDepartment !== null) {
            $departments[$adminDepartment->value] = $adminDepartment;
        }

        foreach ($this->getCourrierIndexDepartments() as $department) {
            $departments[$department->value] = $department;
        }

        return array_values($departments);
    }
}
