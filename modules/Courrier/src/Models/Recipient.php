<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Models;

use AcMarche\Courrier\Database\Factories\RecipientFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[UseFactory(RecipientFactory::class)]
#[Connection('maria-courrier')]
#[Fillable([
    'supervisor_id',
    'slug',
    'last_name',
    'first_name',
    'username',
    'email',
    'receives_attachments',
])]
final class Recipient extends Model
{
    use HasFactory;
    use HasSlug;

    #[Override]
    public $timestamps = false;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['last_name', 'first_name'])
            ->saveSlugsTo('slug');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supervisor_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'supervisor_id');
    }

    public function incomingMails(): BelongsToMany
    {
        return $this->belongsToMany(IncomingMail::class, 'incoming_mail_recipient')
            ->withPivot('is_primary');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'recipient_service');
    }

    protected static function newFactory(): RecipientFactory
    {
        return RecipientFactory::new();
    }

    protected function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    protected function casts(): array
    {
        return [
            'receives_attachments' => 'boolean',
        ];
    }
}
