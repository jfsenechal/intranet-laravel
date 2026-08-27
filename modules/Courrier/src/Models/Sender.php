<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Models;

use AcMarche\Courrier\Database\Factories\SenderFactory;
use AcMarche\Courrier\Repository\DepartmentScope;
use AcMarche\Courrier\Repository\SenderRepository;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Override;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[UseFactory(SenderFactory::class)]
#[ScopedBy([DepartmentScope::class])]
#[Connection('maria-courrier')]
#[Fillable([
    'slug',
    'name',
    'department',
])]
final class Sender extends Model
{
    use HasFactory;
    use HasSlug;

    #[Override]
    public $timestamps = false;

    protected $table = 'courrier_senders';

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function incomingMails(): BelongsToMany
    {
        return $this->belongsToMany(IncomingMail::class, 'incoming_mail_service')
            ->withPivot('is_primary');
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Recipient::class, 'recipient_service');
    }

    /**
     * The datalist of suggestions is cached per department, so a sender saved
     * from the mail form has to drop it or it would not be suggested until the
     * entry expired on its own.
     */
    protected static function booted(): void
    {
        self::saved(function (Sender $sender): void {
            SenderRepository::forgetDatalist($sender->department);
        });

        self::deleted(function (Sender $sender): void {
            SenderRepository::forgetDatalist($sender->department);
        });
    }

    protected static function newFactory(): SenderFactory
    {
        return SenderFactory::new();
    }

    protected function casts(): array
    {
        return [

        ];
    }
}
