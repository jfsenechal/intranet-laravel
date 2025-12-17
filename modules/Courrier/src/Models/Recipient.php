<?php

namespace AcMarche\Courrier\Models;

use AcMarche\Courrier\Database\Factories\RecipientFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[UseFactory(RecipientFactory::class)]
final class Recipient extends Model
{
    use HasFactory;

    protected $connection = 'maria-courrier';

    protected bool $timestamp = false;

    protected $fillable = [
        'supervisor_id',
        'slug',
        'last_name',
        'first_name',
        'username',
        'email',
        'is_active',
        'receives_attachments',
    ];

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

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    protected static function booted(): void
    {
        self::creating(function (Recipient $recipient): void {
            if (empty($recipient->slug)) {
                $recipient->slug = Str::slug($recipient->last_name.'_'.$recipient->first_name);
            }
        });
    }

    protected static function newFactory(): RecipientFactory
    {
        return RecipientFactory::new();
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'receives_attachments' => 'boolean',
        ];
    }
}
