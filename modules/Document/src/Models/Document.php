<?php

declare(strict_types=1);

namespace AcMarche\Document\Models;

use AcMarche\Document\Database\Factories\DocumentFactory;
use AcMarche\Document\Observers\DocumentObserver;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([DocumentObserver::class])]
#[Connection('maria-document')]
#[Fillable([
    'name',
    'content',
    'file_path',
    'file_name',
    'file_size',
    'file_mime',
    'user_add',
    'category_id',
])]
final class Document extends Model
{
    use HasFactory;
    use HasUserAdd;
    use SoftDeletes;

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

    protected static function newFactory(): DocumentFactory
    {
        return DocumentFactory::new();
    }

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }
}
