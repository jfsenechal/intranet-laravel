<?php

declare(strict_types=1);

namespace AcMarche\Publication\Models;

use AcMarche\Publication\Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[Connection('maria-publication')]
#[Fillable([
    'name',
    'url',
    'wpCategoryId',
])]
#[Table(name: 'publication_categories')]
final class Category extends Model
{
    use HasFactory;

    #[Override]
    public $timestamps = false;

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
