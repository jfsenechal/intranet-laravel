<?php

declare(strict_types=1);

namespace AcMarche\Ad\Models;

use AcMarche\Ad\Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[Connection('maria-ad')]
#[Fillable(['name', 'icon', 'color'])]
#[Table(name: 'ad_categories')]
final class Category extends Model
{
    use HasFactory;

    #[Override]
    public $timestamps = false;

    /**
     * @return HasMany<ClassifiedAd>
     */
    public function ad(): HasMany
    {
        return $this->hasMany(ClassifiedAd::class);
    }

    // Model::automaticallyEagerLoadRelationships();

    /**
     * To resolve name
     * static::resolveFactoryName($modelName);
     */
    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
