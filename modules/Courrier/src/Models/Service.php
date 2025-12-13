<?php

namespace AcMarche\Courrier\Models;

use AcMarche\Courrier\Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

final class Service extends Model
{
    use HasFactory;

    protected $connection = 'maria-courrier';

    protected bool $timestamp = false;

    protected $fillable = [
        'slug',
        'name',
        'initials',
        'is_active',
    ];

    public function incomingMails(): BelongsToMany
    {
        return $this->belongsToMany(IncomingMail::class, 'incoming_mail_service')
            ->withPivot('is_primary');
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Recipient::class, 'recipient_service');
    }

    protected static function booted(): void
    {
        self::creating(function (Service $service): void {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    protected static function newFactory(): ServiceFactory
    {
        return ServiceFactory::new();
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
