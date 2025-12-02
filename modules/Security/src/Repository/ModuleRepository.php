<?php

namespace AcMarche\Security\Repository;

use AcMarche\Security\Models\Module;

final class ModuleRepository
{
    public static function find(int $moduleId): ?Module
    {
        return Module::with('roles')->find($moduleId);
    }

    public static function getModulesWithoutTab(): iterable
    {
        return Module::query()
            ->whereNull('tab_id')
            ->get();
    }

    public function getModulesForSelect(): array
    {
        $modules = [];
        foreach (Module::all() as $module) {
            $modules[$module->id] = $module->name;
        }

        return $modules;
    }
}
