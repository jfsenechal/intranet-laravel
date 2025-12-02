<?php

namespace AcMarche\Security\Repository;

use AcMarche\Security\Models\Tab;
use Illuminate\Support\Collection;

final class TabRepository
{
    /**
     * Get all tabs with their modules
     *
     * @return Collection<int,Tab>
     */
    public static function getTabsWithModules(): Collection
    {
        return Tab::with([
            'modules' => function ($query) {
                $query->orderBy('name');
            },
        ])
            ->orderBy('name')
            ->get();
    }
}
