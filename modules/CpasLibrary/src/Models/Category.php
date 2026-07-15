<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Models;

use AcMarche\CpasLibrary\Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[UseFactory(CategoryFactory::class)]
#[Connection('maria-cpas-library')]
#[Fillable([
    'parent_id',
    'name',
    'description',
    'slug',
    'icon',
    'color',
    'departments',
    'public',
    'users',
])]
final class Category extends Model
{
    use HasFactory;

    #[Override]
    public $timestamps = false;

    /**
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Fiche, $this>
     */
    public function fiches(): HasMany
    {
        return $this->hasMany(Fiche::class, 'category_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'departments' => 'array',
            'users' => 'array',
            'public' => 'boolean',
        ];
    }
}
