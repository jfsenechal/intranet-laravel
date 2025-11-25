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
class News extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUserAdd;

    protected $connection = 'maria-news';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author',
        'category',
        'is_published',
        'is_featured',
        'published_at',
        'name',
        'content',
        'end_date',
        'archive',
        'user_add',
        'department',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Category>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

}
