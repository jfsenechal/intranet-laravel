<?php

namespace AcMarche\Document\Models;

use AcMarche\Document\Database\Factories\DocumentFactory;
use AcMarche\Document\Observers\DocumentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[ObservedBy([DocumentObserver::class])]
class Document extends Model
{
    use HasFactory;

    protected $connection = 'maria-document';
    protected $fillable = [
        'name',
        'content',
        'file_name',
        'file_mime',
        'file_size',
        'user_add',
        'category_id',
    ];

    //Model::automaticallyEagerLoadRelationships();

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (Auth::check()) {
                $model->user_add = Auth::user()->username;
            }
        });
    }

    /**
     * To resolve name
     * static::resolveFactoryName($modelName);
     */
    protected static function newFactory(): DocumentFactory
    {
        return DocumentFactory::new();
    }

    /**
     * @return BelongsTo<Category>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

}
