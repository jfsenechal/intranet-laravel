<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\TrainingFactory;
use AcMarche\Hrm\Enums\TrainingTypeEnum;
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
 * @property int $employee_id
 * @property string $name
 * @property string|null $description
 * @property \Carbon\CarbonImmutable|null $start_date
 * @property \Carbon\CarbonImmutable|null $end_date
 * @property \Carbon\CarbonImmutable|null $college_date
 * @property int|null $duration_minutes
 * @property TrainingTypeEnum $training_type
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property string|null $certificate_file
 * @property bool $certificate_received
 * @property \Carbon\CarbonImmutable|null $reminder_date
 * @property string $user_add
 * @property \Carbon\CarbonImmutable|null $certificate_received_at
 * @property string|null $granted_by
 * @property \Carbon\CarbonImmutable|null $granted_at
 * @property string|null $updated_by
 * @property bool|null $is_closed
 * @property-read Employee|null $employee
 */
#[Connection('maria-hrm')]
#[Fillable([
    'employee_id',
    'name',
    'description',
    'start_date',
    'end_date',
    'college_date',
    'reminder_date',
    'duration_minutes',
    'training_type',
    'certificate_file',
    'certificate_received',
    'certificate_received_at',
    'granted_by',
    'granted_at',
    'is_closed',
    'user_add',
    'updated_by',
])]
#[Table(name: 'trainings')]
#[UseFactory(TrainingFactory::class)]
final class Training extends Model
{
    use HasFactory;
    use HasUserAdd;

    public static function formatDuration(?int $minutes): string
    {
        $minutes = (int) $minutes;
        if ($minutes === 0) {
            return '';
        }
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;
        if ($hours === 0) {
            return sprintf('%dmin', $remaining);
        }
        if ($remaining === 0) {
            return sprintf('%dh', $hours);
        }

        return sprintf('%dh %02dmin', $hours, $remaining);
    }

    /**
     * @return BelongsTo<Employee>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
            'college_date' => 'date',
            'reminder_date' => 'date',
            'certificate_received_at' => 'date',
            'granted_at' => 'date',
            'certificate_received' => 'boolean',
            'is_closed' => 'boolean',
            'training_type' => TrainingTypeEnum::class,
        ];
    }
}
