<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Models;

use AcMarche\Telecommunication\Database\Factories\TelephoneFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(TelephoneFactory::class)]
#[Connection('maria-telecommunication')]
#[Fillable([
    'line_type_id',
    'slug',
    'user_name',
    'number',
    'archived',
    'mobistar',
    'proximus',
    'service',
    'department',
    'budget_article',
    'location',
    'fixed_cost',
    'note',
])]
final class Telephone extends Model
{
    use HasFactory;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'archived' => 'boolean',
    ];

    /**
     * @return BelongsTo<LineType, $this>
     */
    public function lineType(): BelongsTo
    {
        return $this->belongsTo(LineType::class);
    }

    /**
     * @return HasMany<Attachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
