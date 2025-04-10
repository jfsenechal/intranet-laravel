<?php

namespace AcMarche\News\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $connection = 'maria-news';

    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'content'];

    /**
     * To resolve name
     * static::resolveFactoryName($modelName);
     */
    protected static function newFactory()
    {
        return NewsFactory::new();
    }

}
