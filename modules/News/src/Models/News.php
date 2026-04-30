<?php

declare(strict_types=1);

namespace AcMarche\News\Models;

use AcMarche\News\Database\Factories\NewsFactory;
use AcMarche\News\Observers\NewsObserver;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[ObservedBy([NewsObserver::class])]
#[Connection('maria-news')]
#[Fillable([
    'title',
    'slug',
    'excerpt',
    'content',
    'author',
    'category',
    'name',
    'content',
    'end_date',
    'archive',
    'user_add',
    'department',
    'category_id',
    'medias',
])]
final class News extends Model
{
    use HasFactory;
    use HasSlug;
    use HasUserAdd;
    use Prunable;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['title'])
            ->saveSlugsTo('slug');
    }

    /**
     * @return BelongsTo<Category>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function prunable(): Builder
    {
        return self::query()->where('published_at', '<', now()->subDays(720));
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }

    protected static function newFactory(): NewsFactory
    {
        return NewsFactory::new();
    }

    protected function casts(): array
    {
        return [
            'archive' => 'boolean',
            'published_at' => 'datetime',
            'medias' => 'array',
        ];
    }
}
