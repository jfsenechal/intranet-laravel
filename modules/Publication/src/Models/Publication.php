<?php

declare(strict_types=1);

namespace AcMarche\Publication\Models;

use AcMarche\Publication\Database\Factories\PublicationFactory;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Connection('maria-publication')]
#[Fillable([
    'category_id',
    'name',
    'url',
    'expire_date',
])]
final class Publication extends Model
{
    use HasFactory;
    use HasUserAdd;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected static function newFactory(): PublicationFactory
    {
        return PublicationFactory::new();
    }

    protected function casts(): array
    {
        return [
            'expire_date' => 'datetime',
        ];
    }
}
