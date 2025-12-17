<?php

namespace AcMarche\Courrier\Models;

use AcMarche\Courrier\Database\Factories\IncomingMailFactory;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(IncomingMailFactory::class)]
final class IncomingMail extends Model
{
    use HasFactory;
    use HasUserAdd;
    use SoftDeletes;

    protected $connection = 'maria-courrier';

    protected $fillable = [
        'category_id',
        'reference_number',
        'sender',
        'description',
        'mail_date',
        'is_notified',
        'is_registered',
        'has_acknowledgment',
        'user_add',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'incoming_mail_service')
            ->using(IncomingMailService::class)
            ->withPivot('is_primary');
    }

    public function primaryService(): BelongsToMany
    {
        return $this->services()->wherePivot('is_primary', true);
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Recipient::class, 'incoming_mail_recipient')
            ->using(IncomingMailRecipient::class)
            ->withPivot('is_primary');
    }

    public function primaryRecipient(): BelongsToMany
    {
        return $this->recipients()->wherePivot('is_primary', true);
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }

    protected static function newFactory(): IncomingMailFactory
    {
        return IncomingMailFactory::new();
    }

    protected function casts(): array
    {
        return [
            'mail_date' => 'date',
            'is_notified' => 'boolean',
            'is_registered' => 'boolean',
            'has_acknowledgment' => 'boolean',
        ];
    }
}
