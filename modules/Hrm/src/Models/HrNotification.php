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
