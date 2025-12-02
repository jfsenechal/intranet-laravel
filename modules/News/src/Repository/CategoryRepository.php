<?php

namespace AcMarche\News\Repository;

use AcMarche\News\Models\Category;
use Illuminate\Support\Collection;

final class CategoryRepository
{
    public static function list(): Collection
    {
        return Category::query()->orderBy('name')->get();
    }
}
