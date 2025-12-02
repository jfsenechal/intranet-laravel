<?php

namespace AcMarche\News\Models;

use AcMarche\News\Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Category extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'maria-news';

    protected $fillable = ['name', 'icon', 'color'];

    /**
     * @return BelongsToMany<News>
     */
    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class);
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
