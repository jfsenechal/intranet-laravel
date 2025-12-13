<?php

namespace AcMarche\Courrier\Models;

use AcMarche\Courrier\Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Category extends Model
{
    use HasFactory;

    protected $connection = 'maria-courrier';

    protected $fillable = [
        'name',
        'color',
    ];

    public function incomingMails(): HasMany
    {
        return $this->hasMany(IncomingMail::class);
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
