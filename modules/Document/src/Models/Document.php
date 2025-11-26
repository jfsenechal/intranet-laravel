<?php



namespace AcMarche\Document\Models;

use AcMarche\Document\Observers\DocumentObserver;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

#[ObservedBy([DocumentObserver::class])]
class Document extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUserAdd;

    protected $connection = 'maria-document';

    protected $fillable = [
        'name',
        'content',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'category',
        'is_published',
        'published_at',
        'user_add',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'file_size' => 'integer',
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
