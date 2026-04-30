<?php

declare(strict_types=1);

namespace AcMarche\Ad\Repository;

use AcMarche\Ad\Models\Category;
use Illuminate\Support\Collection;

final class CategoryRepository
{
    public static function list(): Collection
    {
        return Category::query()->orderBy('name')->get();
    }
}
