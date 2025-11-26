<?php

namespace AcMarche\Publication\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Publication extends Model
{
    use HasUserAdd;

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
