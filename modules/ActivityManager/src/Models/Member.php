<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Models;

use AcMarche\ActivityManager\Database\Factories\MembreFactory;
use AcMarche\ActivityManager\Enums\CiviliteEnum;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[UseFactory(MembreFactory::class)]
#[Connection('maria-activity-manager')]
#[Table(name: 'members')]
#[Fillable([
    'civility',
    'last_name',
    'first_name',
    'street',
    'number',
    'postal_code',
    'city',
    'mobile',
    'phone',
    'email',
    'enabled',
    'remark',
    'registered_at',
])]
final class Member extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsToMany<Schedule, $this>
     */
    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'registrations', 'member_id', 'schedule_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'civility' => CiviliteEnum::class,
            'enabled' => 'boolean',
            'registered_at' => 'date',
        ];
    }
}
