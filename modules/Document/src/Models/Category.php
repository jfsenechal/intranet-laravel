<?php

namespace AcMarche\Document\Models;

use AcMarche\Document\Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Category extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'maria-document';

    protected $fillable = ['name'];

    /**
     * @return BelongsToMany<Document>
     */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class);
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
