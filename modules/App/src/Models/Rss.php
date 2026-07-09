<?php

declare(strict_types=1);

namespace AcMarche\App\Models;

use AcMarche\App\Database\Factories\RssFactory;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'rsses')]
#[Fillable([
    'username',
    'name',
    'url',
])]
#[UseFactory(RssFactory::class)]
final class Rss extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
}
