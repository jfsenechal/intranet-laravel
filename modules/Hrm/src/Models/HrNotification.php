<?php

namespace AcMarche\Hrm\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class HrNotification extends Model
{
    use HasUserAdd;

    protected $connection = 'maria-hrm';

    protected $table = 'hr_notifications';

    protected $fillable = [
        'title',
        'object_id',
        'object_type',
        'employer_id',
        'user_add',
    ];

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
