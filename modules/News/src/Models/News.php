<?php

namespace AcMarche\News\Models;

use AcMarche\News\Observers\NewsObserver;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([NewsObserver::class])]
final class News extends Model
{
    use HasFactory;
    use HasUserAdd;
    use SoftDeletes;

    protected $connection = 'maria-news';

    protected $fillable = [
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
    ];

    /**
     * @return BelongsTo<Category>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();
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
