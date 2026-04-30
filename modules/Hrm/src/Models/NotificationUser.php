<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Connection('maria-hrm')]
#[Fillable([
    'notification_id',
    'user',
])]
#[Table(name: 'notification_users')]
final class NotificationUser extends Model
{
    use HasFactory;

    #[Override]
    public $timestamps = false;

    /**
     * @return BelongsTo<HrNotification>
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(HrNotification::class, 'notification_id');
    }
}
