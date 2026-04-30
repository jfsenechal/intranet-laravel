<?php

declare(strict_types=1);

namespace AcMarche\Pst\Models;

use AcMarche\Pst\Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

#[UseFactory(MediaFactory::class)]
#[Connection('maria-pst')]
#[Fillable([
    'name',
    'action_id',
    'uuid',
    'file_name',
    'mime_type',
    'disk',
    'size',
])]
final class Media extends Model
{
    use HasFactory, Notifiable;

    /**
     * @return BelongsTo<Action>
     */
    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }
}
