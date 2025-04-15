<?php

namespace AcMarche\News\Models;

use AcMarche\News\Database\Factories\NewsFactory;
use AcMarche\News\Observers\NewsObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([NewsObserver::class])]
class News extends Model
{
    use HasFactory;

    protected $connection = 'maria-news';
    protected $fillable = ['name', 'content', 'end_date', 'archive', 'user_add', 'department', 'category_id'];

    //Model::automaticallyEagerLoadRelationships();

    /**
     * To resolve name
     * static::resolveFactoryName($modelName);
     */
    protected static function newFactory(): NewsFactory
    {
        return NewsFactory::new();
    }

    /**
     * @return BelongsTo<Category>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

}
