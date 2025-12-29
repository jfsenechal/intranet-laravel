<?php

namespace AcMarche\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class NotificationUser extends Model
{
    public $timestamps = false;

    protected $connection = 'maria-hrm';

    protected $table = 'notification_users';

    protected $fillable = [
        'notification_id',
        'user',
    ];

    /**
     * @return BelongsTo<HrNotification>
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(HrNotification::class, 'notification_id');
    }
}
