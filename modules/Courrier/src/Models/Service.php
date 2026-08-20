<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Models;

use AcMarche\Courrier\Database\Factories\ServiceFactory;
use AcMarche\Courrier\Models\Concerns\HasDepartmentScope;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Override;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[UseFactory(ServiceFactory::class)]
#[Connection('maria-courrier')]
#[Fillable([
    'slugname',
    'name',
    'initials',
    'department',
])]
#[Table(name: 'courrier_services')]
final class Service extends Model
{
    use HasDepartmentScope;
    use HasFactory;
    use HasSlug;

    #[Override]
    public $timestamps = false;

    public function incomingMails(): BelongsToMany
    {
        return $this->belongsToMany(IncomingMail::class, 'incoming_mail_service')
            ->withPivot('is_primary');
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Recipient::class, 'recipient_service');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slugname');
    }

    /**
     * Detaching keeps the `incoming_mail_service` and `recipient_service` pivots
     * clean: the courriers belong to the Inbox and the recipients are people,
     * both outlive the service they were linked to, so they are unlinked here,
     * never deleted.
     */
    protected static function booted(): void
    {
        self::deleting(function (self $service): void {
            $service->incomingMails()->detach();
            $service->recipients()->detach();
        });
    }

    protected static function newFactory(): ServiceFactory
    {
        return ServiceFactory::new();
    }

    protected function casts(): array
    {
        return [

        ];
    }
}
