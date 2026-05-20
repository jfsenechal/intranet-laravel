<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Models;

use AcMarche\Security\Models\HasUserAdd;
use AcMarche\SportsActivities\Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(RegistrationFactory::class)]
#[Connection('maria-rescam')]
#[Fillable([
    'activity_id',
    'group_id',
    'member_id',
    'price',
    'comment',
])]
final class Registration extends Model
{
    use HasFactory;
    use HasUserAdd;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float',
    ];

    /**
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * @return BelongsTo<Group, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }
}
