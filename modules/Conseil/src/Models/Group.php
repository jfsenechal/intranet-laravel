<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Models;

use AcMarche\Conseil\Database\Factories\GroupFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(GroupFactory::class)]
#[Connection('maria-conseil')]
#[Fillable([
    'name',
])]
final class Group extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsToMany<Recipient, $this>
     */
    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Recipient::class, 'group_recipient');
    }

    /**
     * @return HasMany<Attachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
