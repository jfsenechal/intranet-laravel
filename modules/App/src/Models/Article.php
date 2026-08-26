<?php

declare(strict_types=1);

namespace AcMarche\App\Models;

use AcMarche\App\Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'articles')]
#[Fillable([
    'title',
    'excerpt',
    'body',
])]
#[UseFactory(ArticleFactory::class)]
final class Article extends Model
{
    use HasFactory;
}
