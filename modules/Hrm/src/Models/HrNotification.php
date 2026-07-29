<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int $object_id
 * @property string $object_type
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property string $user_add
 * @property int|null $employeur_id
 * @property-read Employer|null $employer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, NotificationUser> $notificationUsers
 */
#[Connection('maria-hrm')]
#[Fillable([
    'name',
    'object_id',
    'object_type',
    'employer_id',
    'user_add',
])]
#[Table(name: 'hr_notifications')]
final class HrNotification extends Model
{
    use HasFactory;
    use HasUserAdd;

    /**
     * @return BelongsTo<Employer>
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    /**
     * @return HasMany<NotificationUser>
     */
    public function notificationUsers(): HasMany
    {
        return $this->hasMany(NotificationUser::class, 'notification_id');
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }
}
