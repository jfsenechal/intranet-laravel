<?php

namespace AcMarche\News\Models;

use AcMarche\News\Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $connection = 'maria-news';
    public $timestamps = false;
    protected $fillable = ['name'];

    //Model::automaticallyEagerLoadRelationships();

    /**
     * To resolve name
     * static::resolveFactoryName($modelName);
     */
    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }

    /**
     * @return BelongsToMany<News>
     */
    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class);
    }
}
