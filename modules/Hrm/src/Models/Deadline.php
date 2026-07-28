<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\DeadlineFactory;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $employee_id
 * @property string $name
 * @property string|null $note
 * @property bool|null $is_closed
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property \Carbon\CarbonImmutable|null $start_date
 * @property \Carbon\CarbonImmutable|null $end_date
 * @property \Carbon\CarbonImmutable|null $reminder_date
 * @property \Carbon\CarbonImmutable|null $closed_date
 * @property string $user_add
 * @property int|null $employer_id
 * @property int|null $direction_id
 * @property int|null $service_id
 * @property string|null $updated_by
 * @property-read Employee|null $employee
 * @property-read Employer|null $employer
 * @property-read Direction|null $direction
 * @property-read Service|null $service
 */
#[Connection('maria-hrm')]
#[Fillable([
    'employee_id',
    'employer_id',
    'direction_id',
    'service_id',
    'name',
    'note',
    'start_date',
    'end_date',
    'reminder_date',
    'closed_date',
    'is_closed',
    'user_add',
    'updated_by',
])]
#[Table(name: 'deadlines')]
#[UseFactory(DeadlineFactory::class)]
final class Deadline extends Model
{
    use HasFactory;
    use HasUserAdd;

    /**
     * @return BelongsTo<Employee>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<Employer>
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    /**
     * @return BelongsTo<Direction>
     */
    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    /**
     * @return BelongsTo<Service>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'reminder_date' => 'date',
            'closed_date' => 'date',
            'is_closed' => 'boolean',
        ];
    }
}
