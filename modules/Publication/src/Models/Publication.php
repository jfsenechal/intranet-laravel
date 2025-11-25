<?php

namespace AcMarche\Publication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Publication extends Model
{
    protected $connection = 'maria-publication';

    protected $fillable = [
        'category_id',
        'name',
        'url',
        'expire_date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function casts(): array
    {
        return [
            'expire_date' => 'datetime',
        ];
    }
}
