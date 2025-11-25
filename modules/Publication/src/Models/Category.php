<?php

namespace AcMarche\Publication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Category extends Model
{
    public $timestamps = false;
    protected $connection = 'maria-publication';

    protected $fillable = [
        'name',
        'url',
        'wpCategoryId',
    ];

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }
}
